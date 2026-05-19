<?php namespace App\Services\Artists;

use App\Models\Artist;
use Illuminate\Database\QueryException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as LaravelLengthAwarePaginator;

class PaginateArtistTracks
{
    public function execute(Artist $artist): LengthAwarePaginator
    {
        try {
            return $artist
                ->tracks()
                ->with('genres')
                ->withCount(['plays', 'likes', 'reposts'])
                ->paginate(request('perPage') ?? 20);
        } catch (QueryException $exception) {
            return new LaravelLengthAwarePaginator(
                collect(),
                0,
                request('perPage') ?? 20,
                1,
            );
        }
    }
}
