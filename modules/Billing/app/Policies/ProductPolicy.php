<?php

namespace Modules\Billing\Policies;

use App\Models\User;
use Modules\Billing\Models\Product;

class ProductPolicy
{
    public function create(User $user): bool
    {
        return ! is_demo_mode();
    }

    public function update(User $user, Product $product): bool
    {
        return ! is_demo_mode();
    }

    public function delete(User $user, Product $product): bool
    {
        return ! is_demo_mode();
    }

    public function deleteAny(User $user): bool
    {
        return ! is_demo_mode();
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return ! is_demo_mode();
    }

    public function forceDeleteAny(User $user): bool
    {
        return ! is_demo_mode();
    }

    public function restore(User $user, Product $product): bool
    {
        return ! is_demo_mode();
    }

    public function restoreAny(User $user): bool
    {
        return ! is_demo_mode();
    }

    public function reorder(User $user): bool
    {
        return ! is_demo_mode();
    }
}
