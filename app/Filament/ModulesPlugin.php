<?php

namespace App\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\Str;
use InterNACHI\Modular\Support\ModuleRegistry;

class ModulesPlugin implements Plugin
{
    public function getId(): string
    {
        return 'modules';
    }

    public function register(Panel $panel): void
    {
        $useTopNavigation = config('filament.modules.clusters.enabled', false) && config('filament.modules.clusters.use-top-navigation', false);
        $panel->topNavigation($useTopNavigation);

        $plugins = $this->getModulePlugins();

        // Register each module plugin
        foreach ($plugins as $pluginClass) {
            $panel->plugin($pluginClass::make());
        }
    }

    public function boot(Panel $panel): void
    {
        $plugins = $this->getModulePlugins();

        // Collect custom navigation items from module plugins
        $navigationItems = [];
        foreach ($plugins as $pluginClass) {
            $plugin = $pluginClass::make();

            // Check if the plugin has getNavigationItems method
            if (method_exists($plugin, 'getNavigationItems')) {
                $items = $plugin->getNavigationItems();
                if (! empty($items)) {
                    $navigationItems = array_merge($navigationItems, $items);
                }
            }
        }

        // Register collected custom navigation items
        if (! empty($navigationItems)) {
            $panel->navigationItems($navigationItems);
        }
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

    protected function getModulePlugins(): array
    {
        $modules = app(ModuleRegistry::class)->modules();

        return $modules
            ->map(function ($module) {
                $moduleName = Str::studly($module->name);
                $pluginClass = "Modules\\{$moduleName}\\Filament\\{$moduleName}Plugin";

                return class_exists($pluginClass) ? $pluginClass : null;
            })
            ->filter()
            ->toArray();
    }
}
