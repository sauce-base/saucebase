<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use InterNACHI\Modular\Support\ModuleRegistry;

abstract class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [];

    final protected function moduleName(): string
    {
        return app(ModuleRegistry::class)->moduleForClass(static::class)->name;
    }

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        // TODO: remove once https://github.com/InterNACHI/modular/pull/117 is merged —
        // internachi currently only discovers lang/ inside resources/, but modules keep it at root.
        $this->registerTranslations();
        $this->registerConfig();
        $this->shareInertiaData();
    }

    /**
     * Register the service providers.
     */
    public function register(): void
    {
        foreach ($this->providers as $provider) {
            $this->app->register($provider);
        }
    }

    protected function registerTranslations(): void
    {
        $langPath = module_path($this->moduleName(), 'lang');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleName());
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $configPath = 'config/config.php';

        if (! file_exists(module_path($this->moduleName(), $configPath))) {
            return;
        }

        $this->publishes([module_path($this->moduleName(), $configPath) => config_path($this->moduleName().'.php')], $configPath);
        $this->mergeConfigFrom(module_path($this->moduleName(), $configPath), $this->moduleName());
    }

    /**
     * Fully replace a config key with a module config file.
     *
     * Unlike mergeConfigFrom, this overwrites the entire key,
     * avoiding array-merge issues with numeric-keyed arrays.
     */
    protected function replaceConfig(string $path, string $key): void
    {
        $this->app['config']->set($key, require module_path($this->moduleName(), $path));
    }

    /**
     * Share Inertia data globally.
     */
    protected function shareInertiaData(): void {}
}
