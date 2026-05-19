<?php namespace App\Services\Artists;

use App\Models\Artist;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PaginateArtistAlbums
{
    /**
     * Paginate all specified artist's albums.
     *
     * First order by number of tracks, so all albums
     * with less than 5 tracks (singles) are at
     * the bottom, then order by album release date.
     */
    public function execute(Artist $artist, array $params): AbstractPaginator
    {
        $withTracks = castToBoolean(Arr::get($params, 'loadAlbumTracks', true));
        $perPage =
            (int) ($params['albumsPerPage'] ?? ($params['perPage'] ?? 5));

        try {
            return $this->paginateAlbums($artist, $withTracks, $perPage, true, Arr::get($params, 'paginate') === 'simple');
        } catch (QueryException $exception) {
            return $this->paginateAlbums($artist, false, $perPage, false, Arr::get($params, 'paginate') === 'simple');
        }
    }

    protected function paginateAlbums(
        Artist $artist,
        bool $withTracks,
        int $perPage,
        bool $orderByTrackCount,
        bool $simple,
    ): AbstractPaginator {
        $prefix = DB::getTablePrefix();

        $builder = $artist
            ->albums()
            ->with($withTracks ? ['artists', 'tracks.artists'] : ['artists'])
            ->orderByRaw(
                $orderByTrackCount
                    ? "tracks_count >= 5 desc, {$prefix}albums.release_date desc, {$prefix}albums.id desc"
                    : "{$prefix}albums.release_date desc, {$prefix}albums.id desc",
            );

        if ($simple) {
            return $builder->simplePaginate($perPage);
        }

        return $builder->paginate($perPage);
    }
}
