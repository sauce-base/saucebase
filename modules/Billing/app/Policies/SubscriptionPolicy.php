<?php

namespace Modules\Billing\Policies;

use App\Models\User;
use Modules\Billing\Models\Subscription;

class SubscriptionPolicy
{
    public function update(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->customer->user_id;
    }
}
