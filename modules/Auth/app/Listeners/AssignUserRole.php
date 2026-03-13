<?php

namespace Modules\Auth\Listeners;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class AssignUserRole
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        /** @var User $user */
        $user = $event->user;

        if ($user->roles->isEmpty()) {
            $user->syncRoles([Role::USER->value]);
        }
    }
}
