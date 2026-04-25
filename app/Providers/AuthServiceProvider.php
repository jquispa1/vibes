<?php

namespace App\Providers;

use App\Policies\BlogPostPolicy;
use App\Policies\MusicUploadPolicy;
use App\Policies\TrackCommentPolicy;
use App\Models\BlogPost;
use Common\Comments\Comment;
use Common\Files\FileEntry;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $policies = [
        FileEntry::class => MusicUploadPolicy::class,
        Comment::class => TrackCommentPolicy::class,
        BlogPost::class => BlogPostPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
