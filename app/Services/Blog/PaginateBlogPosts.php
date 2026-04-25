<?php

namespace App\Services\Blog;

use App\Models\BlogPost;
use Common\Database\Datasource\Datasource;
use Illuminate\Pagination\AbstractPaginator;

class PaginateBlogPosts
{
    public function execute(array $params, $builder = null): AbstractPaginator
    {
        if (!$builder) {
            $builder = BlogPost::query();
        }

        $builder->with('author');

        $datasource = new Datasource($builder, $params);

        return $datasource->paginate();
    }
}