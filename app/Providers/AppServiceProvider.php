<?php

namespace App\Providers;

use Illuminate\Foundation\Events\DiscoverEvents;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;

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

        /**
         * Fix for event discovery paths in modules
         *
         * @link https://github.com/nWidart/laravel-modules/issues/2128#issuecomment-3515275319
         */
        $this->fixDiscoverEventsModulePathIssue();
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
            $this->app['router']->pushMiddlewareToGroup('web', \App\Http\Middleware\SecureHeaders::class);
        }
    }

    protected function fixDiscoverEventsModulePathIssue(): void
    {
        DiscoverEvents::guessClassNamesUsing(function (\SplFileInfo $file, $basePath) {
            $pathname = $file->getRealPath() ?: $file->getPathname();
            $class = trim(Str::replaceFirst($basePath, '', $pathname), DIRECTORY_SEPARATOR);

            // Check if this is a module file and skip if module is disabled
            $modulesPath = config('modules.paths.modules');

            if ($modulesPath && str_starts_with($pathname, $modulesPath.DIRECTORY_SEPARATOR)) {
                // Extract module name from path (e.g., "/path/to/modules/Auth/..." -> "Auth")
                $relativePath = Str::after($pathname, $modulesPath.DIRECTORY_SEPARATOR);
                $moduleName = Str::before($relativePath, DIRECTORY_SEPARATOR);

                if ($moduleName && Module::find($moduleName)?->isEnabled() === false) {
                    return null;
                }
            }

            // Remove the "app" folder from the path if it exists (useful for module structures)
            $appFolder = Str::of(config('modules.app_folder', 'app/'))
                ->start(DIRECTORY_SEPARATOR)
                ->finish(DIRECTORY_SEPARATOR);

            return ucfirst(Str::camel(str_replace(
                [$appFolder, DIRECTORY_SEPARATOR, ucfirst(basename(app()->path())).'\\'],
                ['\\', '\\', app()->getNamespace(), ''],
                ucfirst(Str::replaceLast('.php', '', $class))
            )));
        });
    }
}
