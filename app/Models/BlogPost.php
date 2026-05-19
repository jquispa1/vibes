<?php namespace App\Models;

use App\Models\BlogCategory;
use Common\Core\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Str;

class BlogPost extends BaseModel
{
    use HasFactory;

    const MODEL_TYPE = 'blog_post';

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'published_at' => 'datetime',
    ];

    protected $appends = ['model_type'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            BlogCategory::class,
            'blog_category_blog_post',
            'blog_post_id',
            'blog_category_id',
        );
    }

    public function scopePublished($query)
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getFeaturedImageAttribute($value)
    {
        if (!$value || Str::startsWith($value, ['http://', 'https://', '//'])) {
            return $value;
        }

        return url($value);
    }

    public function toNormalizedArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->title,
            'image' => $this->featured_image,
            'description' => $this->excerpt,
            'model_type' => self::MODEL_TYPE,
        ];
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'author' => $this->author?->name,
            'status' => $this->status,
        ];
    }

    public static function filterableFields(): array
    {
        return ['id', 'user_id', 'status'];
    }

    public static function getModelTypeAttribute(): string
    {
        return BlogPost::MODEL_TYPE;
    }
}