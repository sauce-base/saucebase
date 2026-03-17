<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function create(User $user): bool
    {
        return ! is_demo_mode();
    }

    public function update(User $user, User $model): bool
    {
        return ! is_demo_mode();
    }

    public function delete(User $user, User $model): bool
    {
        return ! is_demo_mode();
    }

    public function deleteAny(User $user): bool
    {
        return ! is_demo_mode();
    }
}
