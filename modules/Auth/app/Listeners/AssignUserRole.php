<?php

namespace Modules\Auth\Listeners;

use App\Enums\Role;
use Illuminate\Auth\Events\Registered;

class AssignUserRole
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        /** @var \App\Models\User $user */
        $user = $event->user;

        if ($user->roles->isEmpty()) {
            $user->syncRoles([Role::USER->value]);
        }
    }
}
