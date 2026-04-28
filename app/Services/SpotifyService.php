<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SpotifyService
{
    protected string $clientId;
    protected string $clientSecret;
    protected ?string $token = null;
    protected ?int $tokenExpiresAt = null;

    public function __construct()
    {
        $this->clientId = config('services.spotify.client_id');
        $this->clientSecret = config('services.spotify.client_secret');
    }

    protected function getToken(): string
    {
        if ($this->token && $this->tokenExpiresAt && time() < $this->tokenExpiresAt) {
            return $this->token;
        }

        $response = Http::asForm()
            ->withBasicAuth($this->clientId, $this->clientSecret)
            ->post('https://accounts.spotify.com/api/token', [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Unable to fetch Spotify token');
        }

        $data = $response->json();
        $this->token = $data['access_token'] ?? null;
        $this->tokenExpiresAt = time() + (($data['expires_in'] ?? 3600) - 60);

        return $this->token;
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

    public function getPlaylist(string $playlistId): array
    {
        $token = $this->getToken();

        $resp = Http::withToken($token)
            ->get("https://api.spotify.com/v1/playlists/{$playlistId}");

        if ($resp->failed()) {
            throw new \RuntimeException('Spotify playlist fetch failed: ' . $resp->status());
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
