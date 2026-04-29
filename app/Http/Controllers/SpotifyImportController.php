<?php namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Track;
use App\Services\SpotifyService;
use Common\Core\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpotifyImportController extends BaseController
{
    public function __construct(protected SpotifyService $spotifyService)
    {
    }

    /**
     * Retorna los datos de una playlist pública de Spotify usando solo
     * Client Credentials (sin que el usuario se loguee en Spotify).
     *
     * GET /api/v1/spotify/playlists/{playlistId}
     */
    // DEBUG TEMPORAL - borrar después de diagnosticar
    public function debug(Request $request)
    {
        $results = [];

        // 1. Credenciales configuradas?
        $results['config'] = [
            'client_id_set'     => !empty(config('services.spotify.client_id')),
            'client_secret_set' => !empty(config('services.spotify.client_secret')),
            'client_id_prefix'  => substr(config('services.spotify.client_id') ?? '', 0, 6) . '...',
        ];

        // 2. Obtener token de app
        try {
            $token = $this->spotifyService->getAppToken();
            $results['token'] = [
                'ok'     => true,
                'prefix' => substr($token, 0, 10) . '...',
            ];
        } catch (\Throwable $e) {
            $results['token'] = ['ok' => false, 'error' => $e->getMessage()];
            return $this->success($results);
        }

        // 3. Llamada raw a Spotify con una playlist pública conocida
        $testId = $request->query('id', '3cEYpjA9oz9GiPac4AsH4n'); // playlist pública de Spotify
        $resp = \Illuminate\Support\Facades\Http::withToken($token)
            ->get("https://api.spotify.com/v1/playlists/{$testId}");

        $results['raw_request'] = [
            'playlist_id' => $testId,
            'status'      => $resp->status(),
            'headers'     => $resp->headers(),
        ];

        $body = $resp->json();
        $results['raw_response'] = [
            'error'       => $body['error'] ?? null,
            'id'          => $body['id'] ?? null,
            'name'        => $body['name'] ?? null,
            'has_items'   => isset($body['items']),
            'has_tracks'  => isset($body['tracks']),
            'items_total' => $body['items']['total'] ?? $body['tracks']['total'] ?? null,
            'first_item'  => isset($body['items']['items'][0])
                ? ($body['items']['items'][0]['item']['name'] ?? $body['items']['items'][0]['track']['name'] ?? 'unknown')
                : (isset($body['tracks']['items'][0])
                    ? ($body['tracks']['items'][0]['item']['name'] ?? $body['tracks']['items'][0]['track']['name'] ?? 'unknown')
                    : null),
        ];

        return $this->success($results);
    }

    public function showPublic(Request $request, string $playlistId)
    {
        $playlistId = SpotifyService::extractPlaylistId($playlistId);

        if (!$playlistId) {
            return $this->error(__('spotify.invalid_playlist'), [], 422);
        }

        try {
            $data = $this->spotifyService->getPublicPlaylist($playlistId);
        } catch (\RuntimeException $exception) {
            return match ($exception->getCode()) {
                403 => $this->error(__('spotify.playlist_not_accessible'), [], 403),
                404 => $this->error(__('spotify.invalid_playlist'), [], 404),
                default => $this->error($exception->getMessage(), [], 422),
            };
        }

        // Devolver solo los campos útiles (sin inflar la respuesta)
        // Nota: desde feb 2026 Spotify renombró "tracks" → "items" en playlists
        $itemsRoot = $data['items'] ?? $data['tracks'] ?? [];
        return $this->success([
            'playlist' => [
                'id'          => $data['id'],
                'name'        => $data['name'],
                'description' => $data['description'],
                'public'      => $data['public'],
                'image'       => $data['images'][0]['url'] ?? null,
                'owner'       => [
                    'id'           => $data['owner']['id'],
                    'display_name' => $data['owner']['display_name'],
                ],
                'tracks_total' => $itemsRoot['total'] ?? count($data['all_tracks'] ?? []),
                'tracks'       => collect($data['all_tracks'] ?? [])->map(function ($item) {
                    $track = $item['track'] ?? null;
                    if (empty($track['id'])) return null;
                    return [
                        'id'          => $track['id'],
                        'name'        => $track['name'],
                        'duration_ms' => $track['duration_ms'],
                        'explicit'    => $track['explicit'],
                        'artists'     => collect($track['artists'] ?? [])->pluck('name'),
                        'album'       => $track['album']['name'] ?? null,
                        'image'       => $track['album']['images'][0]['url'] ?? null,
                        'added_at'    => $item['added_at'],
                        'spotify_url' => $track['external_urls']['spotify'] ?? null,
                    ];
                })->filter()->values(),
                'spotify_url' => $data['external_urls']['spotify'] ?? null,
            ],
        ]);
    }

    public function start(Request $request)
    {
        $this->validate($request, [
            'url' => 'required|string',
        ]);

        $state = Str::random(40);
        $request->session()->put('spotify_import_url', $request->input('url'));
        $request->session()->put('spotify_import_state', $state);

        return redirect()->away($this->spotifyService->getAuthorizationUrl($state));
    }

    public function callback(Request $request)
    {
        // LOG TEMPORAL DE DEBUG
        \Illuminate\Support\Facades\Log::info('Spotify callback hit', [
            'has_code'  => $request->filled('code'),
            'has_state' => $request->filled('state'),
            'has_error' => $request->filled('error'),
            'user_id'   => $request->user()?->id,
            'session_state' => $request->session()->get('spotify_import_state') ? 'present' : 'missing',
            'session_url'   => $request->session()->get('spotify_import_url') ? 'present' : 'missing',
        ]);

        if ($request->filled('error')) {
            return redirect('/spotify/import?spotify_import_error=' . urlencode(__('spotify.oauth_denied')));
        }

        $storedState = $request->session()->pull('spotify_import_state');
        $storedUrl = $request->session()->pull('spotify_import_url');

        if (!$storedState || !$request->filled('state') || !hash_equals($storedState, $request->string('state')->toString())) {
            return redirect('/spotify/import?spotify_import_error=' . urlencode(__('spotify.oauth_state_invalid')));
        }

        if (!$storedUrl) {
            return redirect('/spotify/import?spotify_import_error=' . urlencode(__('spotify.invalid_playlist')));
        }

        $playlistId = SpotifyService::extractPlaylistId($storedUrl);
        if (!$playlistId) {
            return redirect('/spotify/import?spotify_import_error=' . urlencode(__('spotify.invalid_playlist')));
        }

        try {
            \Illuminate\Support\Facades\Log::info('Spotify: exchanging code', ['playlist' => $playlistId]);
            $accessToken = $this->spotifyService->exchangeAuthorizationCode($request->input('code'));
            \Illuminate\Support\Facades\Log::info('Spotify token exchanged', ['token_prefix' => substr($accessToken, 0, 10)]);

            // Obtener el perfil del usuario para debug
            $meResp = \Illuminate\Support\Facades\Http::withToken($accessToken)->get('https://api.spotify.com/v1/me');
            \Illuminate\Support\Facades\Log::info('Spotify user profile raw', [
                'status' => $meResp->status(),
                'body'   => $meResp->body(),
            ]);
            $data = $this->spotifyService->getPlaylist($playlistId, $accessToken);
            \Illuminate\Support\Facades\Log::info('Spotify playlist fetched', ['name' => $data['name'] ?? 'unknown', 'tracks' => count($data['all_tracks'] ?? [])]);
            $result = $this->importPlaylist($request->user(), $data);
        } catch (\Throwable $exception) {
            \Illuminate\Support\Facades\Log::error('Spotify import failed', [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'class'   => get_class($exception),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
            ]);
            return redirect('/spotify/import?spotify_import_error=' . urlencode($this->mapSpotifyExceptionToMessage($exception)));
        }

        return redirect('/spotify/import?spotify_import=success&attached=' . $result['attached'] . '&total=' . $result['total']);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'url' => 'required|string',
        ]);

        $user = $request->user();
        if (!$user) {
            return $this->error('Unauthenticated', [], 401);
        }

        $playlistId = SpotifyService::extractPlaylistId($request->input('url'));
        if (!$playlistId) {
            return $this->error(__('spotify.invalid_playlist'), [], 422);
        }

        try {
            $data = $this->spotifyService->getPlaylist($playlistId);
        } catch (\RuntimeException $exception) {
            if ($exception->getCode() === 403) {
                return $this->error(__('spotify.playlist_not_accessible'), [], 422);
            }

            if ($exception->getCode() === 404) {
                return $this->error(__('spotify.invalid_playlist'), [], 422);
            }

            return $this->error($exception->getMessage(), [], 422);
        }

        if (isset($data['public']) && !$data['public']) {
            return $this->error(__('spotify.playlist_must_be_public'), [], 422);
        }

        $result = $this->importPlaylist($user, $data);

        return $this->success(['playlist' => $result['playlist'], 'attached' => $result['attached'], 'total' => $result['total']]);
    }

    protected function importPlaylist($user, array $data): array
    {
        $playlist = new Playlist([
            'name' => $data['name'] ?? 'Spotify playlist',
            'description' => $data['description'] ?? null,
            'public' => true,
            'image' => $data['images'][0]['url'] ?? null,
            'spotify_id' => $data['id'] ?? null,
        ]);

        $playlist->owner()->associate($user);
        $playlist->save();

        $position = 0;
        $attached = 0;
        $total = count($data['all_tracks'] ?? []);

        foreach ($data['all_tracks'] as $item) {
            $trackData = $item['track'] ?? $item;
            if (empty($trackData['id'])) {
                continue;
            }

            $spotifyTrackId = $trackData['id'];

            $track = Track::where('spotify_id', $spotifyTrackId)->first();
            if (!$track) {
                $track = Track::create([
                    'name' => $trackData['name'] ?? 'Unknown',
                    'duration' => $trackData['duration_ms'] ?? 0,
                    'spotify_id' => $spotifyTrackId,
                ]);
            }

            $playlist->tracks()->attach($track->id, ['position' => $position]);
            $position++;
            $attached++;
        }

        return [
            'playlist' => $playlist->fresh('tracks'),
            'attached' => $attached,
            'total' => $total,
        ];
    }

    protected function mapSpotifyExceptionToMessage(\Throwable $exception): string
    {
        if ($exception instanceof \RuntimeException) {
            if ($exception->getCode() === 403) {
                return __('spotify.playlist_not_accessible');
            }

            if ($exception->getCode() === 404) {
                return __('spotify.invalid_playlist');
            }
        }

        return $exception->getMessage() ?: __('spotify.playlist_not_accessible');
    }
}
