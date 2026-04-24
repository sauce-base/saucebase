<?php

namespace App\Providers;

use App\Http\Middleware\SecureHeaders;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureSecureUrls();
    }

    protected function configureSecureUrls(): void
    {
        // Determine if HTTPS should be enforced
        $enforceHttps = $this->app->environment(['production', 'staging'])
            && ! $this->app->runningUnitTests();

        // For local development with SSL setup
        $localHttps = $this->app->environment('local')
            && config('app.url')
            && str_starts_with(config('app.url'), 'https://')
            && ! $this->app->runningUnitTests();

        $useHttps = $enforceHttps || $localHttps;

        // Force HTTPS for all generated URLs
        URL::forceHttps($useHttps);

        // Ensure proper server variable is set
        if ($useHttps) {
            $this->app['request']->server->set('HTTPS', 'on');
        }

        // Set up global middleware for security headers in production/staging
        if ($enforceHttps) {
            $this->app['router']->pushMiddlewareToGroup('web', SecureHeaders::class);
        }
    }
}
