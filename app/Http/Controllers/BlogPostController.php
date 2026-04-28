<?php namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\Blog\PaginateBlogPosts;
use Common\Core\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class BlogPostController extends BaseController
{
    public function __construct(
        protected BlogPost $blogPost,
        protected BlogCategory $blogCategory,
        protected Request $request,
    ) {
    }

    public function index()
    {
        $query = $this->blogPost->newQuery()->with(['author', 'categories']);

        if ($category = $this->request->string('category')->toString()) {
            $query->whereHas('categories', function ($builder) use ($category) {
                $builder->where('slug', $category);
            });
        }

        if (
            $this->request->user()?->hasPermission('admin.access') &&
            !$this->request->boolean('publishedOnly')
        ) {
            $pagination = app(PaginateBlogPosts::class)->execute(
                $this->request->all(),
                $query,
            );
        } else {
            $pagination = $query
                ->published()
                ->latest('published_at')
                ->paginate($this->request->integer('perPage', 15));
        }

        return $this->renderClientOrApi([
            'pageName' => 'blog-page',
            'data' => [
                'pagination' => $pagination,
            ],
        ]);
    }

    public function show(BlogPost $blogPost)
    {
        return $this->renderClientOrApi([
            'pageName' => 'blog-post-page',
            'data' => [
                'blogPost' => $blogPost->load('author', 'categories'),
            ],
        ]);
    }

    public function showCategory(BlogCategory $blogCategory)
    {
        return $this->renderClientOrApi([
            'pageName' => 'blog-category-page',
            'data' => [
                'blogCategory' => $blogCategory->loadCount('blogPosts'),
            ],
        ]);
    }

    public function store()
    {
        $this->authorize('store', BlogPost::class);

        $this->validate($this->request, [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(['draft', 'published'])],
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'published_at' => 'nullable|date',
            'categories' => 'nullable|array',
        ]);

        $data = $this->request->only([
            'title',
            'slug',
            'excerpt',
            'content',
            'featured_image',
            'status',
            'meta_title',
            'meta_description',
            'published_at',
        ]);

        $data['slug'] = slugify($data['slug'] ?: $data['title']);
        $data['meta_title'] = $data['meta_title'] ?: $data['title'];
        $data['meta_description'] = $data['meta_description'] ?: ($data['excerpt'] ?? null);
        $data['user_id'] = $this->request->user()?->id;

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = Carbon::now();
        }

        $blogPost = $this->blogPost->create($data);
        $this->syncCategories($blogPost);

        return $this->success(['blogPost' => $blogPost->load('author', 'categories')]);
    }

    public function update(BlogPost $blogPost)
    {
        $this->authorize('update', $blogPost);

        $this->validate($this->request, [
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('blog_posts', 'slug')->ignore($blogPost->id),
            ],
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(['draft', 'published'])],
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'published_at' => 'nullable|date',
            'categories' => 'nullable|array',
        ]);

        $data = $this->request->only([
            'title',
            'slug',
            'excerpt',
            'content',
            'featured_image',
            'status',
            'meta_title',
            'meta_description',
            'published_at',
        ]);

        $data['slug'] = slugify($data['slug'] ?: $data['title']);
        $data['meta_title'] = $data['meta_title'] ?: $data['title'];
        $data['meta_description'] = $data['meta_description'] ?: ($data['excerpt'] ?? null);

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = Carbon::now();
        }

        $blogPost->update($data);
        $this->syncCategories($blogPost);

        return $this->success(['blogPost' => $blogPost->fresh(['author', 'categories'])]);
    }

    protected function syncCategories(BlogPost $blogPost): void
    {
        $categories = $this->request->get('categories', []);
        $categoryIds = $this->blogCategory->insertOrRetrieve($categories)->pluck('id');

        $blogPost->categories()->sync($categoryIds);
    }

    public function destroy(string $ids)
    {
        $blogPostIds = explode(',', $ids);
        $this->authorize('destroy', [BlogPost::class, $blogPostIds]);

        $count = $this->blogPost->destroy($blogPostIds);

        return $this->success(['count' => $count]);
    }
}