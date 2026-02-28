<?php

namespace Modules\Auth\Http\Controllers;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Lab404\Impersonate\Services\ImpersonateManager;

class ReimpersonateController extends Controller
{
    /**
     * Re-impersonate a user from recent history.
     */
    public function __invoke(int $userId): RedirectResponse
    {
        $impersonate = app(ImpersonateManager::class);
        $guard = Filament::getCurrentOrDefaultPanel()->getAuthGuard();

        // If already impersonating, leave first to get back to the impersonator
        if ($impersonate->isImpersonating()) {
            $impersonate->leave();
        }

        $impersonator = Filament::auth()->user();

        abort_if(! $impersonator, 403, 'Impersonator not authenticated');
        // Security check: cannot impersonate yourself
        abort_if($userId === $impersonator->id, 403, 'Cannot impersonate yourself');

        $target = User::findOrFail($userId);
        // Store session data (like the Filament Impersonate package does)
        // Preserve existing back_to value when re-impersonating from history
        session()->put([
            'impersonate.back_to' => session('impersonate.back_to') ?? Filament::getCurrentOrDefaultPanel()->getUrl(),
            'impersonate.guard' => $guard,
        ]);

        // Perform impersonation with guard (triggers TakeImpersonation event automatically)
        $impersonate->take($impersonator, $target, $guard);

        return redirect(config('filament-impersonate.redirect_to'));
    }
}
