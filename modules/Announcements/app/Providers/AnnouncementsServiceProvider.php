<?php

namespace Modules\Announcements\Providers;

use App\Providers\ModuleServiceProvider;
use Inertia\Inertia;
use Modules\Announcements\Models\Announcement;

class AnnouncementsServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Announcements';

    protected string $nameLower = 'announcements';

    protected array $providers = [
        RouteServiceProvider::class,
    ];

    protected function shareInertiaData(): void
    {
        Inertia::share('announcement', function () {
            $cookieName = config('announcements.cookie_name');
            $dismissedId = request()->cookie($cookieName);
            $announcement = Announcement::active()->first();

            if (! $announcement) {
                return null;
            }

            if ($dismissedId && (int) $dismissedId === $announcement->id) {
                return null;
            }

            return $announcement->only([
                'id',
                'text',
                'is_dismissable',
                'show_on_frontend',
                'show_on_dashboard',
            ]);
        });
    }
}
