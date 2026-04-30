<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SpotifyService
{
    protected string $clientId;
    protected string $clientSecret;
    protected ?string $importApiUrl = null;
    protected ?string $importApiHost = null;

    public function __construct()
    {
        $this->clientId = config('services.spotify.client_id');
        $this->clientSecret = config('services.spotify.client_secret');
        $this->importApiUrl = config('services.spotify.import_api_url');
        $this->importApiHost = config('services.spotify.import_api_host');
    }

    public function getAuthorizationUrl(string $state): string
    {
        return 'https://accounts.spotify.com/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => route('spotify.import.callback'),
            'scope' => 'playlist-read-private playlist-read-collaborative',
            'state' => $state,
        ]);
    }

    /**
     * Obtiene un token de app (Client Credentials) cacheado en Redis/Cache.
     * No requiere que el usuario esté autenticado con Spotify.
     * Sirve para leer cualquier playlist pública.
     */
    public function getAppToken(): string
    {
        return Cache::remember('spotify_app_token', 3540, function () {
            $response = Http::asForm()
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->post('https://accounts.spotify.com/api/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->failed()) {
                throw new \RuntimeException('Unable to fetch Spotify app token');
            }

            return $response->json('access_token');
        });
    }

    /**
     * @deprecated Usar getAppToken() para playlists públicas.
     * Mantenido por compatibilidad con el flujo OAuth de usuario.
     */
    protected function getToken(): string
    {
        return $this->getAppToken();
    }

    /**
     * Obtiene una playlist pública de Spotify sin necesidad de que el usuario
     * esté autenticado. Usa el token de app (Client Credentials).
     *
     * @throws \RuntimeException si la playlist no existe, no es pública o hay error de API
     */
    public function getPublicPlaylist(string $playlistId): array
    {
        return $this->getPlaylist($playlistId, $this->getAppToken());
    }

    public function exchangeAuthorizationCode(string $code): string
    {
        $response = Http::asForm()
            ->withBasicAuth($this->clientId, $this->clientSecret)
            ->post('https://accounts.spotify.com/api/token', [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => route('spotify.import.callback'),
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Unable to exchange Spotify authorization code', $response->status());
        }

        $data = $response->json();
        $token = $data['access_token'] ?? null;

        if (!$token) {
            throw new \RuntimeException('Spotify authorization did not return an access token');
        }

        return $token;
    }

    public static function extractPlaylistId(string $input): ?string
    {
        $input = trim($input);
        // spotify URI
        if (str_starts_with($input, 'spotify:playlist:')) {
            return substr($input, strlen('spotify:playlist:'));
        }

        // URL like https://open.spotify.com/playlist/{id}
        $parts = parse_url($input);
        if ($parts && isset($parts['path'])) {
            $segments = explode('/', trim($parts['path'], '/'));
            $key = end($segments);
            // remove query params if any
            $key = explode('?', $key)[0];
            return $key;
        }

        // plain id
        return $input ?: null;
    }

    public function getPlaylist(string $playlistId, ?string $token = null): array
    {
        // If an external import API is configured, use it instead of calling Spotify
        if (!empty($this->importApiUrl)) {
            $playlistUrl = "https://open.spotify.com/playlist/{$playlistId}";
            $headers = [
                'Host' => $this->importApiHost ?: 'vibeturn.com',
                'Accept' => 'application/json',
            ];
            $requestUrl = rtrim($this->importApiUrl, '?') . '?url=' . rawurlencode($playlistUrl);

            $resp = Http::withHeaders($headers)->get($requestUrl);

            if ($resp->failed()) {
                \Illuminate\Support\Facades\Log::error('External import API failed', [
                    'status' => $resp->status(),
                    'body' => $resp->body(),
                    'headers' => $headers,
                    'url' => $this->importApiUrl,
                    'playlist' => $playlistId,
                ]);

                throw new \RuntimeException('External playlist fetch failed: ' . $resp->status(), $resp->status());
            }

            $data = $resp->json();

            // If external API wraps response under 'playlist', unwrap it
            if (isset($data['playlist'])) {
                $data = $data['playlist'];
            }

            // If external API returns a plain tracks array, normalize to expected shape
            if (isset($data['tracks']) && !isset($data['all_tracks'])) {
                $allTracks = [];
                foreach ($data['tracks'] as $t) {
                    $track = $t;

                    // If API didn't include numeric id, try to extract it from the uri
                    if (empty($track['id']) && !empty($track['uri'])) {
                        // uri formats: spotify:track:{id} or https://.../track/{id}
                        if (preg_match('/(?:spotify:track:|track\/)([A-Za-z0-9]+)/', $track['uri'], $m)) {
                            $track['id'] = $m[1];
                        } elseif (preg_match('/([^:\/]+)$/', $track['uri'], $m)) {
                            $track['id'] = $m[1];
                        }
                    }

                    $allTracks[] = ['track' => $track, 'added_at' => $t['added_at'] ?? null];
                }
                $data['all_tracks'] = $allTracks;
            }

            return $data;
        }

        $token = $token ?: $this->getToken();

        $resp = Http::withToken($token)
            ->get("https://api.spotify.com/v1/playlists/{$playlistId}");

        if ($resp->failed()) {
            \Illuminate\Support\Facades\Log::error('Spotify getPlaylist failed', [
                'status'   => $resp->status(),
                'body'     => $resp->body(),
                'playlist' => $playlistId,
            ]);

            if ($resp->status() === 403) {
                throw new \RuntimeException(__('spotify.playlist_not_accessible'), 403);
            }

            if ($resp->status() === 404) {
                throw new \RuntimeException(__('spotify.invalid_playlist'), 404);
            }

            throw new \RuntimeException('Spotify playlist fetch failed: ' . $resp->status(), $resp->status());
        }

        $data = $resp->json();

        // Febrero 2026: Spotify renombró "tracks" → "items" en la respuesta de playlist.
        // Además, dentro de cada item el campo "track" pasó a llamarse "item".
        // Solo se devuelven canciones si el token es del dueño/colaborador de la playlist.
        $allTracks = [];

        // Soportamos tanto la API nueva (items) como la antigua (tracks) por si acaso
        $rootKey   = isset($data['items']) ? 'items' : 'tracks';
        $firstPage = $data[$rootKey]['items'] ?? [];

        foreach ($firstPage as $entry) {
            $allTracks[] = $this->normalizeTrackEntry($entry);
        }

        $next = $data[$rootKey]['next'] ?? null;
        while ($next) {
            $page = Http::withToken($token)->get($next);
            if ($page->failed()) break;
            $pj = $page->json();
            foreach ($pj['items'] ?? [] as $entry) {
                $allTracks[] = $this->normalizeTrackEntry($entry);
            }
            $next = $pj['next'] ?? null;
        }

        $data['all_tracks'] = array_filter($allTracks); // quitar nulls (tracks locales, etc.)
        return $data;
    }

    /**
     * Normaliza una entrada de playlist al formato unificado {track, added_at}.
     * Soporta tanto la respuesta antigua (entry.track) como la nueva (entry.item).
     */
    protected function normalizeTrackEntry(?array $entry): ?array
    {
        if (empty($entry)) return null;

        // API nueva usa "item", la antigua usaba "track"
        $trackData = $entry['item'] ?? $entry['track'] ?? null;

        // Ignorar tracks locales o entradas vacías
        if (empty($trackData['id'])) return null;

        return [
            'track'    => $trackData, // mantenemos clave "track" para no romper importPlaylist()
            'added_at' => $entry['added_at'] ?? null,
            'added_by' => $entry['added_by'] ?? null,
        ];
    }
}
