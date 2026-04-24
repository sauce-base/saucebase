<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use InterNACHI\Modular\Support\ModuleRegistry;

class BreadcrumbServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * Note: Core routes/breadcrumbs.php is automatically loaded by the package.
     * This provider only loads module-specific breadcrumb files.
     */
    public function boot(): void
    {
        // Load module breadcrumbs
        $this->loadModuleBreadcrumbs();
    }

    /**
     * Load breadcrumbs from a file.
     */
    protected function loadBreadcrumbsFrom(string $path): void
    {
        if (file_exists($path)) {
            require_once $path;
        }
    }

    /**
     * Load breadcrumbs from enabled modules.
     */
    protected function loadModuleBreadcrumbs(): void
    {
        app(ModuleRegistry::class)->modules()->each(function ($module): void {
            $this->loadBreadcrumbsFrom($module->path('routes/breadcrumbs.php'));
        });
    }
}
