<?php

namespace Modules\Themes\Providers;

use App\Providers\ModuleServiceProvider;
use Inertia\Inertia;
use Modules\Themes\Console\Commands\ApplyThemeCommand;
use Modules\Themes\Services\ThemeService;

class ThemesServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Themes';

    protected string $nameLower = 'themes';

    protected array $providers = [
        RouteServiceProvider::class,
    ];

    protected array $commands = [
        ApplyThemeCommand::class,
    ];

    protected function shareInertiaData(): void
    {
        Inertia::share('themes', fn () => config('themes.enabled', true) ? [
            'items' => ThemeService::discoverThemes(),
            'fonts' => [
                'sans' => ThemeService::loadFonts('sans'),
                'serif' => ThemeService::loadFonts('serif'),
                'mono' => ThemeService::loadFonts('mono'),
            ],
        ] : null);
    }
}
