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
            $accessToken = $this->spotifyService->exchangeAuthorizationCode($request->input('code'));
            $data = $this->spotifyService->getPlaylist($playlistId, $accessToken);
            $result = $this->importPlaylist($request->user(), $data);
        } catch (\Throwable $exception) {
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
