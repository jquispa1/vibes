<?php namespace App\Http\Controllers;

use App\Models\BlogCategory;
use Common\Core\BaseController;
use Common\Database\Datasource\Datasource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlogCategoryController extends BaseController
{
    public function __construct(
        protected BlogCategory $blogCategory,
        protected Request $request,
    ) {
    }

    public function index()
    {
        $this->authorize('index', BlogCategory::class);

        $pagination = (new Datasource(
            $this->blogCategory->newQuery(),
            $this->request->all(),
        ))->paginate();

        return $this->success(['pagination' => $pagination]);
    }

    public function store()
    {
        $this->authorize('store', BlogCategory::class);

        $data = $this->validate($this->request, [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:blog_categories,slug',
        ]);

        $category = $this->blogCategory->create([
            'name' => $data['name'],
            'slug' => slugify($data['slug']),
        ]);

        return $this->success(['blogCategory' => $category]);
    }

    public function update(BlogCategory $blogCategory)
    {
        $this->authorize('update', $blogCategory);

        $data = $this->validate($this->request, [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('blog_categories', 'name')->ignore($blogCategory->id),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('blog_categories', 'slug')->ignore($blogCategory->id),
            ],
        ]);

        $blogCategory->update([
            'name' => $data['name'],
            'slug' => slugify($data['slug']),
        ]);

        return $this->success(['blogCategory' => $blogCategory->fresh()]);
    }

    public function destroy(string $ids)
    {
        $blogCategoryIds = explode(',', $ids);
        $this->authorize('destroy', [BlogCategory::class, $blogCategoryIds]);

        $count = $this->blogCategory->destroy($blogCategoryIds);

        return $this->success(['count' => $count]);
    }
}