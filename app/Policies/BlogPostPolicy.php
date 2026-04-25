<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;
use Common\Core\Policies\BasePolicy;

class BlogPostPolicy extends BasePolicy
{
    public function index(?User $user)
    {
        return $this->hasPermission($user, 'admin.access');
    }

    public function store(User $user)
    {
        return $this->hasPermission($user, 'admin.access');
    }

    public function update(User $user, BlogPost $blogPost)
    {
        return $this->hasPermission($user, 'admin.access');
    }

    public function destroy(User $user, $blogPostIds)
    {
        return $this->hasPermission($user, 'admin.access');
    }
}