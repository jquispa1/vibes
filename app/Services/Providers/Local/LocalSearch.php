<?php namespace App\Services\Providers\Local;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Channel;
use App\Models\Genre;
use App\Models\Playlist;
use App\Models\Tag;
use App\Models\Track;
use App\Models\User;
use App\Services\Search\SearchInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LocalSearch implements SearchInterface
{
    protected string $query;
    protected int $perPage;
    protected int $page;

    public function search(
        string $q,
        int $page,
        int $perPage,
        array $modelTypes,
    ): Collection {
        $this->query = urldecode($q);
        $this->perPage = $perPage ?: 10;
        $this->page = $page;

        $results = collect();

        $useLike = $this->hasShortWords();

        foreach ($modelTypes as $modelType) {
            if ($modelType === Artist::MODEL_TYPE) {
                $results['artists'] = $useLike
                    ? $this->likeQuery(Artist::class)->simplePaginate($this->perPage, ['*'], 'page', $this->page)
                    : $this->artists();
            } elseif ($modelType === Album::MODEL_TYPE) {
                $paginator = $useLike
                    ? $this->likeQuery(Album::class)->simplePaginate($this->perPage, ['*'], 'page', $this->page)
                    : $this->albums();
                $results['albums'] = $paginator->tap(fn($p) => $p->load(['artists']));
            } elseif ($modelType === Track::MODEL_TYPE) {
                $paginator = $useLike
                    ? $this->likeQuery(Track::class)->simplePaginate($this->perPage, ['*'], 'page', $this->page)
                    : $this->tracks();
                $results['tracks'] = $paginator->tap(fn($p) => $p->load(['album', 'artists']));
            } elseif ($modelType === Playlist::MODEL_TYPE) {
                $paginator = $useLike
                    ? $this->likeQuery(Playlist::class)->simplePaginate($this->perPage, ['*'], 'page', $this->page)
                    : $this->playlists();
                $results['playlists'] = $paginator->tap(fn($p) => $p->load(['editors']));
            } elseif ($modelType === Channel::MODEL_TYPE) {
                $results['channels'] = $this->channels();
            } elseif ($modelType === Genre::MODEL_TYPE) {
                $results['genres'] = $this->genres();
            } elseif ($modelType === Tag::MODEL_TYPE) {
                $results['tags'] = $this->tags();
            } elseif ($modelType === User::MODEL_TYPE) {
                $paginator = $useLike
                    ? $this->likeQuery(User::class)->simplePaginate($this->perPage, ['*'], 'page', $this->page)
                    : $this->users();
                $results['users'] = $paginator->tap(fn($p) => $p->loadCount('followers'));
            }
        }

        return $results;
    }

    public function artists(): Paginator
    {
        return $this->searchOrFallback(Artist::class, fn($q) => Artist::search($q));
    }

    public function albums(): Paginator
    {
        return $this->searchOrFallback(Album::class, fn($q) => Album::search($q), fn($p) => $p->load(['artists']));
    }

    public function tracks(): Paginator
    {
        return $this->searchOrFallback(Track::class, fn($q) => Track::search($q), fn($p) => $p->load(['album', 'artists']));
    }

    public function playlists(): Paginator
    {
        return $this->searchOrFallback(Playlist::class, fn($q) => Playlist::search($q), fn($p) => $p->load(['editors']));
    }

    public function channels(): Paginator
    {
        return app(Channel::class)
            ->search($this->query)
            ->simplePaginate($this->perPage, 'page', $this->page);
    }

    public function genres(): Paginator
    {
        return app(Genre::class)->simplePaginate(
            $this->perPage,
            'page',
            $this->page,
        );
    }

    public function tags(): Paginator
    {
        return app(Tag::class)
            ->search($this->query)
            ->simplePaginate($this->perPage, 'page', $this->page);
    }

    public function users(): Paginator
    {
        return app(User::class)
            ->search($this->query)
            ->simplePaginate($this->perPage, 'page', $this->page)
            ->tap(fn($p) => $p->loadCount('followers'));
    }

    private function searchOrFallback(string $modelClass, callable $scoutQuery, ?callable $tap = null): Paginator
    {
        $q = $this->query;
        $results = $scoutQuery($q)->simplePaginate($this->perPage, 'page', $this->page);
        if ($results->isEmpty()) {
            $results = $this->likeQuery($modelClass)->simplePaginate($this->perPage, ['*'], 'page', $this->page);
        }
        if ($tap) {
            $results->tap($tap);
        }
        return $results;
    }

    private function hasShortWords(): bool
    {
        foreach (explode(' ', trim($this->query)) as $word) {
            if (mb_strlen($word) > 0 && mb_strlen($word) < 3) {
                return true;
            }
        }
        return false;
    }

    private function likeQuery(string $modelClass)
    {
        $q = $this->query;
        if ($modelClass === User::class) {
            $like = $q.'%';
            return $modelClass::where(function($b) use ($q, $like) {
                $b->where('first_name', 'LIKE', $like)
                  ->orWhere('last_name', 'LIKE', $like)
                  ->orWhere('email', 'LIKE', $like);
            })->orderByRaw("CASE WHEN CONCAT(first_name,' ',last_name) = ? THEN 0 ELSE 1 END, LENGTH(CONCAT(first_name,' ',last_name)) ASC", [$q]);
        }
        $like = $q.'%';
        return $modelClass::where('name', 'LIKE', $like)
            ->orderByRaw("CASE WHEN name = ? THEN 0 ELSE 1 END, LENGTH(name) ASC", [$q]);
    }
}
