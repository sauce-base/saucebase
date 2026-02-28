<?php

namespace Modules\Auth\Listeners;

use Illuminate\Auth\Events\Login;

class UpdateUserLastLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        $user->update([
            'last_login_at' => now(),
        ]);
    }
}
