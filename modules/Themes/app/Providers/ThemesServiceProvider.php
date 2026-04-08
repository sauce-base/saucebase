<?php

namespace Modules\Themes\Providers;

use App\Providers\ModuleServiceProvider;
use Inertia\Inertia;
use Modules\Themes\Console\Commands\ApplyThemeCommand;

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
            'items' => $this->discoverThemes(),
            'fonts' => [
                'sans' => $this->loadFonts('sans'),
                'serif' => $this->loadFonts('serif'),
                'mono' => $this->loadFonts('mono'),
            ],
        ] : null);
    }

    /**
     * @return list<array{family: string, category: string, variants: list<string>, variable?: bool}>
     */
    private function loadFonts(string $category): array
    {
        $file = module_path('Themes', "resources/fonts/{$category}.json");
        $content = file_get_contents($file);

        return $content !== false ? json_decode($content, true) ?? [] : [];
    }

    /**
     * @return array<int, array{id: string, name: string, description: string, light: array<string, string>, dark: array<string, string>, editable: bool}>
     */
    private function discoverThemes(): array
    {
        $shipped = glob(module_path('Themes', 'resources/themes').'/*.json') ?: [];
        $userDir = storage_path('app/themes');
        $user = is_dir($userDir) ? (glob($userDir.'/*.json') ?: []) : [];

        sort($shipped);
        sort($user);

        // Shipped palettes first; user palettes appended. Deduplicate by id — user wins.
        $byId = [];
        foreach ($shipped as $file) {
            $parsed = $this->parseThemeFile($file);
            if ($parsed !== null) {
                $parsed['editable'] = false;
                $byId[$parsed['id']] = $parsed;
            }
        }
        foreach ($user as $file) {
            $parsed = $this->parseThemeFile($file);
            if ($parsed !== null) {
                $parsed['editable'] = true;
                $byId[$parsed['id']] = $parsed;
            }
        }

        $themes = array_values($byId);

        usort($themes, fn ($a, $b) => ($a['id'] === 'default' ? -1 : ($b['id'] === 'default' ? 1 : 0)));

        return $themes;
    }

    /**
     * @return array{id: string, name: string, description: string, light: array<string, string>, dark: array<string, string>}|null
     *                                                                                                                              Note: callers are responsible for adding the `editable` key after calling this method.
     */
    private function parseThemeFile(string $file): ?array
    {
        if (! is_file($file)) {
            return null;
        }

        $content = file_get_contents($file);

        if ($content === false) {
            return null;
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode($content, true);

        if (! is_array($data)) {
            return null;
        }

        if (empty($data['name']) || ! isset($data['cssVars'])) {
            return null;
        }

        /** @param array<string, string> $vars */
        $prefixKeys = static function (array $vars): array {
            $result = [];
            foreach ($vars as $key => $value) {
                $result['--'.$key] = $value;
            }

            return $result;
        };

        /** @var array<string, string> $themeVars */
        $themeVars = isset($data['cssVars']['theme']) && is_array($data['cssVars']['theme'])
            ? $prefixKeys($data['cssVars']['theme'])
            : [];

        /** @var array<string, string> $lightVars */
        $lightVars = isset($data['cssVars']['light']) && is_array($data['cssVars']['light'])
            ? $prefixKeys($data['cssVars']['light'])
            : [];

        /** @var array<string, string> $darkVars */
        $darkVars = isset($data['cssVars']['dark']) && is_array($data['cssVars']['dark'])
            ? $prefixKeys($data['cssVars']['dark'])
            : [];

        return [
            'id' => (string) $data['name'],
            'name' => isset($data['title']) ? (string) $data['title'] : ucfirst(basename($file, '.json')),
            'description' => isset($data['description']) ? (string) $data['description'] : '',
            'light' => array_merge($themeVars, $lightVars),
            'dark' => array_merge($themeVars, $darkVars),
        ];
    }
}
