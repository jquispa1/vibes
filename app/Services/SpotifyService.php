<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SpotifyService
{
    protected string $clientId;
    protected string $clientSecret;

    public function __construct()
    {
        $this->clientId = config('services.spotify.client_id');
        $this->clientSecret = config('services.spotify.client_secret');
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
        $token = $token ?: $this->getToken();

        $resp = Http::withToken($token)
            ->get("https://api.spotify.com/v1/playlists/{$playlistId}");

        if ($resp->failed()) {
            if ($resp->status() === 403) {
                throw new \RuntimeException(__('spotify.playlist_not_accessible'), 403);
            }

            if ($resp->status() === 404) {
                throw new \RuntimeException(__('spotify.invalid_playlist'), 404);
            }

            throw new \RuntimeException('Spotify playlist fetch failed: ' . $resp->status(), $resp->status());
        }

        $data = $resp->json();

        // paginate through tracks
        $tracks = [];
        $items = $data['tracks']['items'] ?? [];
        foreach ($items as $it) {
            $tracks[] = $it;
        }

        $next = $data['tracks']['next'] ?? null;
        while ($next) {
            $page = Http::withToken($token)->get($next);
            if ($page->failed()) break;
            $pj = $page->json();
            foreach ($pj['items'] ?? [] as $it) {
                $tracks[] = $it;
            }
            $next = $pj['next'] ?? null;
        }

        $data['all_tracks'] = $tracks;
        return $data;
    }
}
