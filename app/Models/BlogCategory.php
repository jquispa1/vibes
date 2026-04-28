<?php namespace App\Models;

use Common\Core\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class BlogCategory extends BaseModel
{
    use HasFactory;

    const MODEL_TYPE = 'blog_category';

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
    ];

    protected $appends = ['model_type'];

    public function blogPosts(): BelongsToMany
    {
        return $this->belongsToMany(
            BlogPost::class,
            'blog_category_blog_post',
            'blog_category_id',
            'blog_post_id',
        );
    }

    public function insertOrRetrieve(array|Collection $categories): Collection
    {
        if (!$categories instanceof Collection) {
            $categories = collect($categories);
        }

        return $categories
            ->filter()
            ->map(function ($category) {
                if (is_object($category)) {
                    $category = (array) $category;
                }

                if (
                    is_array($category) &&
                    !empty($category['id']) &&
                    is_numeric($category['id'])
                ) {
                    return $this->find($category['id']);
                }

                $name = is_array($category)
                    ? ($category['name'] ?? $category['description'] ?? null)
                    : $category;

                if (!$name) {
                    return null;
                }

                $displayName = is_array($category)
                    ? ($category['description'] ?? $category['name'] ?? $name)
                    : $name;

                return $this->firstOrCreate(
                    ['name' => slugify($name)],
                    ['display_name' => $displayName],
                );
            })
            ->filter()
            ->values();
    }

    public function toNormalizedArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->display_name ?: $this->name,
            'description' => $this->display_name ?: $this->name,
            'model_type' => self::MODEL_TYPE,
        ];
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
        ];
    }

    public static function filterableFields(): array
    {
        return ['id'];
    }

    public static function getModelTypeAttribute(): string
    {
        return self::MODEL_TYPE;
    }
}