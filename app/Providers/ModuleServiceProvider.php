<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

abstract class ModuleServiceProvider extends ServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name;

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower;

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [];

    /**
     * Create a new service provider instance.
     */
    public function __construct($app)
    {
        if (! isset($this->name, $this->nameLower)) {
            throw new \LogicException('Module service provider must define both $name and $nameLower properties.');
        }

        parent::__construct($app);
    }

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerFactories();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
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

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands($this->commands);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        if (! method_exists($this, 'configureSchedules')) {
            return;
        }

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $this->configureSchedules($schedule);
        });
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'));
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $configPath = 'config/config.php';

        if (! file_exists(module_path($this->name, $configPath))) {
            return;
        }

        $this->publishes([module_path($this->name, $configPath) => config_path($this->nameLower.'.php')], $configPath);
        $this->mergeConfigFrom(module_path($this->name, $configPath), $this->nameLower);
    }

    /**
     * Register model factories.
     */
    protected function registerFactories(): void
    {
        Factory::guessFactoryNamesUsing(function (string $modelName) {
            $moduleNamespacePrefix = rtrim((string) config('modules.namespace', 'Modules\\'), '\\') . '\\';
            if (str_starts_with($modelName, $moduleNamespacePrefix)) {

                // get the first part of the string before Models and get the last part of the string for the model name
                $modelNameArr = explode('\\', $modelName);

                // get index of Models
                $index = array_search('Models', $modelNameArr);

                // get the first part of the string before Models index value
                $moduleNamespace = implode('\\', array_slice($modelNameArr, 0, $index));

                // get the last part of the string for the model name
                $modelNameModel = end($modelNameArr);

                return $moduleNamespace.'\\Database\\Factories\\'.$modelNameModel.'Factory';
            }

            return 'Database\\Factories\\'.Str::afterLast($modelName, '\\').'Factory';
        });
    }

    /**
     * Share Inertia data globally.
     */
    protected function shareInertiaData(): void
    {
        // Share defined data
        // Inertia::share('key', 'value');
    }
}
