<?php namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Track;
use App\Services\SpotifyService;
use Common\Core\BaseController;
use Illuminate\Http\Request;

class SpotifyImportController extends BaseController
{
    public function __construct(protected SpotifyService $spotifyService)
    {
        $this->middleware('auth:sanctum');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'url' => 'required|string',
        ]);

        $user = $request->user();
        if (!$user) {
            return $this->error('Unauthenticated', 401);
        }

        $playlistId = SpotifyService::extractPlaylistId($request->input('url'));
        if (!$playlistId) {
            return $this->error(__('spotify.invalid_playlist'), 422);
        }

        $data = $this->spotifyService->getPlaylist($playlistId);

        if (isset($data['public']) && !$data['public']) {
            return $this->error(__('spotify.playlist_must_be_public'), 422);
        }

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

        return $this->success(['playlist' => $playlist->fresh('tracks'), 'attached' => $attached, 'total' => $total]);
    }
}
