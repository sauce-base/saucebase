<?php

namespace Modules\Blog\Policies;

use App\Models\User;
use Modules\Blog\Models\Post;

class PostPolicy
{
    public function create(User $user): bool
    {
        return ! is_demo_mode();
    }

    public function update(User $user, Post $post): bool
    {
        return ! is_demo_mode();
    }

    public function delete(User $user, Post $post): bool
    {
        return ! is_demo_mode();
    }

    public function deleteAny(User $user): bool
    {
        return ! is_demo_mode();
    }
}
