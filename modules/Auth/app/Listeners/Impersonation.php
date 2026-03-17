<?php

namespace Modules\Auth\Listeners;

use App\Helpers\Toast;
use App\Models\User;
use STS\FilamentImpersonate\Events\EnterImpersonation;

class Impersonation
{
    /**
     * Handle the event.
     */
    public function handle(EnterImpersonation $event): void
    {
        /** @var User $impersonated */
        $impersonated = $event->impersonated;

        Toast::info(
            message: trans(
                'You are now impersonating: :name',
                [
                    'name' => $impersonated->name,
                ]
            ),
            description: $impersonated->email,
            position: 'bottom-center'
        );

        // Store in recent history
        $this->storeInRecentHistory($impersonated->id);
    }

    /**
     * Store user ID in recent impersonation history.
     */
    protected function storeInRecentHistory(int $userId): void
    {
        $history = session()->get('impersonation.recent_history', []);

        // Remove if exists (to move to front)
        $history = array_values(array_diff($history, [$userId]));

        // Add to front
        array_unshift($history, $userId);

        // Keep only last 4 (so we have 3 after filtering current user)
        $history = array_slice($history, 0, 4);

        session()->put('impersonation.recent_history', $history);
    }
}
