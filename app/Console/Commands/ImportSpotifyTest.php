<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SpotifyService;

class ImportSpotifyTest extends Command
{
    protected $signature = 'spotify:import-test {url?}';
    protected $description = 'Test external Spotify import API by fetching a playlist URL';

    public function handle()
    {
        $url = $this->argument('url') ?? 'https://open.spotify.com/playlist/37i9dQZF1DXcBWIGoYBM5M';
        $playlistId = SpotifyService::extractPlaylistId($url);

        if (!$playlistId) {
            $this->error('Invalid playlist URL/ID');
            return 1;
        }

        $service = app(SpotifyService::class);

        try {
            $data = $service->getPlaylist($playlistId);
        } catch (\Throwable $e) {
            $this->error('Fetch failed: ' . $e->getMessage());
            return 2;
        }

        $tracks = $data['all_tracks'] ?? [];
        $this->info('Playlist: ' . ($data['name'] ?? 'unknown') . ' — tracks: ' . count($tracks));

        $this->info('First 10 tracks:');
        foreach (array_slice($tracks, 0, 10) as $i => $entry) {
            $track = $entry['track'] ?? $entry;
            $this->line(sprintf("%2d. %s — %s", $i + 1, $track['id'] ?? '[no-id]', $track['name'] ?? 'unknown'));
        }

        return 0;
    }
}
