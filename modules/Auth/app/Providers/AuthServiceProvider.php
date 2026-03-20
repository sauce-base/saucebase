<?php

namespace Modules\Auth\Providers;

use App\Models\User;
use App\Providers\ModuleServiceProvider;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use STS\FilamentImpersonate\ImpersonateManager;

class AuthServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Auth';

    protected string $nameLower = 'auth';

    protected array $providers = [
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Share Inertia data globally.
     */
    protected function shareInertiaData(): void
    {
        Inertia::share('auth.user', fn () => Auth::user());
        Inertia::share('auth.last_social_provider', fn () => request()->cookie('last_social_provider'));

        Inertia::share('impersonation', function () {
            if (! $this->isUserImpersonated()) {
                return null;
            }

            /** @var User $user */
            $user = Auth::user();

            /** @var Role|null $role */
            $role = $user->roles->first();

            return [
                'user' => [
                    ...$user->only(['id', 'name', 'email', 'avatar']),
                    'role' => $role?->name,
                ],
                'route' => route('filament-impersonate.leave'),
                'label' => __('Stop Impersonation'),
                'recent' => $this->getRecentImpersonationHistory(),
            ];
        });
    }

    protected function isUserImpersonated(): bool
    {
        if (! Auth::user()) {
            return false;
        }

        $impersonate = app(ImpersonateManager::class);
        $impersonatorGuard = $impersonate->getImpersonatorGuardUsingName();
        $currentPanelGuard = Filament::getAuthGuard();
        $isImpersonating = $impersonate->isImpersonating();

        return $isImpersonating
            && $currentPanelGuard
            && $impersonatorGuard === $currentPanelGuard;
    }

    /**
     * Get recent impersonation history with user data.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getRecentImpersonationHistory(): array
    {
        $historyIds = session()->get('impersonation.recent_history', []);

        if (empty($historyIds)) {
            return [];
        }

        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUserId = $currentUser->id;

        // Fetch users (filters deleted users automatically)
        $users = User::with('roles:name')
            ->whereIn('id', $historyIds)
            ->where('id', '!=', $currentUserId)
            ->get(['id', 'name', 'email', 'avatar'])
            ->keyBy('id');

        // Maintain original chronological order
        $orderedUsers = [];
        foreach ($historyIds as $id) {
            if ($users->has($id) && $id !== $currentUserId) {
                /** @var User $user */
                $user = $users->get($id);
                /** @var Role|null $userRole */
                $userRole = $user->roles->first();
                $orderedUsers[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'role' => $userRole?->name,
                ];

                // Limit to 3 users
                if (count($orderedUsers) >= 3) {
                    break;
                }
            }
        }

        return $orderedUsers;
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        parent::registerConfig();

        $this->mergeConfigFrom(module_path($this->name, 'config/services.php'), 'services');
    }
}
