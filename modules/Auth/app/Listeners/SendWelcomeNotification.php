<?php

namespace Modules\Auth\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Modules\Auth\Notifications\WelcomeNotification;

class SendWelcomeNotification
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        /** @var User $user */
        $user = $event->user;

        $user->notify(new WelcomeNotification);
    }
}
