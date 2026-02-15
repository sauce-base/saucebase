<?php

namespace App\Providers;

use App\Facades\Navigation as NavigationFacade;
use App\Services\Navigation;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
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
        // Bind our custom Navigation class as scoped (fresh per request)
        $this->app->scoped(Navigation::class, function ($app) {
            return new Navigation($app->make(ActiveUrlChecker::class));
        });

        // Also bind Spatie's class to our implementation so existing DI still works
        $this->app->alias(Navigation::class, \Spatie\Navigation\Navigation::class);

        // Register global alias for the facade
        AliasLoader::getInstance(['Navigation' => NavigationFacade::class]);

        // Auto-load navigation files when the Navigation instance is resolved
        $this->app->resolving(Navigation::class, function (Navigation $navigation): Navigation {
            return $navigation->load();
        });
    }
}
