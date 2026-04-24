<?php

namespace App\Filament;

use Filament\Panel;

trait ModulePlugin
{
    abstract public function getModuleName(): string;

    public function register(Panel $panel): void
    {
        // Build base paths and namespaces
        $appPath = module_path($this->getModuleName(), 'src');
        $baseNamespace = 'Modules\\'.$this->getModuleName();

        $useClusters = config('filament.modules.clusters.enabled', false);

        $panel->discoverPages(
            in: $appPath.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Pages',
            for: $baseNamespace.'\\Filament\\Pages'
        );
        $panel->discoverResources(
            in: $appPath.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Resources',
            for: $baseNamespace.'\\Filament\\Resources'
        );
        $panel->discoverWidgets(
            in: $appPath.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Widgets',
            for: $baseNamespace.'\\Filament\\Widgets'
        );

        $panel->discoverLivewireComponents(
            in: $appPath.DIRECTORY_SEPARATOR.'Livewire',
            for: $baseNamespace.'\\Livewire'
        );

        if ($useClusters) {
            $panel->discoverClusters(
                in: $appPath.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Clusters',
                for: $baseNamespace.'\\Filament\\Clusters',
            );
        }

        $this->afterRegister($panel);
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function afterRegister(Panel $panel)
    {
        // override this to implement additional logic
    }
}
