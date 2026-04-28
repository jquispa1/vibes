<?php

namespace App\Policies;

use App\Models\BlogCategory;
use App\Models\User;
use Common\Core\Policies\BasePolicy;

class BlogCategoryPolicy extends BasePolicy
{
    public function index(?User $user)
    {
        return $this->hasPermission($user, 'admin.access');
    }

    public function store(User $user)
    {
        return $this->hasPermission($user, 'admin.access');
    }

    public function update(User $user, BlogCategory $blogCategory)
    {
        return $this->hasPermission($user, 'admin.access');
    }

    public function destroy(User $user, $blogCategoryIds)
    {
        return $this->hasPermission($user, 'admin.access');
    }
}