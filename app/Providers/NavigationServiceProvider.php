<?php

namespace App\Providers;

use App\Services\Navigation;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Spatie\Navigation\Facades\Navigation as NavigationFacade;
use Spatie\Navigation\Helpers\ActiveUrlChecker;

class NavigationServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        // Navigation is now shared via HandleInertiaRequests middleware
        // This ensures it's evaluated after all navigation items are registered
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // Override Spatie's scoped binding with our custom Navigation class
        $this->app->scoped(\Spatie\Navigation\Navigation::class, function ($app) {
            return new Navigation($app->make(ActiveUrlChecker::class));
        });

        // Register global alias so IDE Helper can generate facade stubs
        AliasLoader::getInstance(['Navigation' => NavigationFacade::class]);

        $this->app->resolving(Navigation::class, function (Navigation $navigation): Navigation {
            return $navigation->load();
        });
    }
}
