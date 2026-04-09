<?php

namespace Modules\Themes\Services;

class ThemeService
{
    public const BUNDLE_THEMES_DIR = 'resources/themes';

    public const USER_THEMES_DIR = 'app/themes';

    public const FONTS_DIR = 'resources/fonts';

    // TODO: move command logic that reads/parses theme files into this service so it can be reused by the command and the controller

    /**
     * Get the file path for a user-defined theme JSON file based on the given filename.
     *
     * @param  string  $filename  The name of the theme file (without extension, e.g. "blueberry")
     * @return string The full file path to the user theme JSON file
     */
    public static function getUserThemePath(string $filename): string
    {
        return static::getUserThemesDir()."/{$filename}.json";
    }

    /**
     * Get the file path for a theme JSON file in the default themes directory based on the given filename.
     *
     * @param  string  $filename  The name of the theme file (without extension, e.g. "blueberry")
     * @return string The full file path to the default theme JSON file
     */
    public static function getBundleThemePath(string $filename): string
    {
        return static::getBundleThemesDir()."/{$filename}.json";
    }

    /**
     * Check if a theme with the given name exists in either the user themes directory or the default themes directory.
     *
     * @param  string  $name  The name of the theme to check (e.g. "blueberry")
     * @return bool True if the theme exists, false otherwise
     */
    public static function themeExists(string $name): bool
    {
        return file_exists(static::getUserThemesDir()."/{$name}.json")
            || file_exists(static::getBundleThemesDir()."/{$name}.json");
    }

    /**
     * Load font metadata from a JSON file for the specified category (e.g. "sans", "serif", "mono").
     *  The JSON file should contain an array of font objects with properties such as family, category, variants, and variable.
     *
     * @param  string  $category  The font category (e.g. "sans", "serif", "mono")
     * @return list<array{family: string, category: string, variants: list<string>, variable?: bool}>
     */
    public static function loadFonts(string $category): array
    {
        $file = module_path('Themes', self::FONTS_DIR."/{$category}.json");
        $content = file_get_contents($file);

        return $content !== false ? json_decode($content, true) ?? [] : [];
    }

    /**
     * Get the directory path where default theme JSON files are stored within the module's resources. These themes are typically read-only and shipped with the application.
     *
     * @return string The full directory path for default themes
     */
    public static function getBundleThemesDir(): string
    {
        return module_path('Themes', self::BUNDLE_THEMES_DIR);
    }

    /**
     * Get the directory path where user-defined theme JSON files are stored. This is typically a writable directory within the application's storage path.
     *
     * @return string The full directory path for user-defined themes
     */
    public static function getUserThemesDir(): string
    {
        return storage_path(self::USER_THEMES_DIR);
    }

    /**
     * Discover available themes by scanning both the default themes directory and the user themes directory.
     * User themes take precedence over default themes with the same name. The returned list is sorted with the "default" theme first (if it exists) and the rest in no particular order.
     *
     * @return array<int, array{id: string, name: string, description: string, light: array<string, string>, dark: array<string, string>, editable: bool}>
     */
    public static function discoverThemes(): array
    {
        $shipped = glob(static::getBundleThemesDir().'/*.json') ?: [];
        $userDir = static::getUserThemesDir();
        $user = is_dir($userDir) ? (glob($userDir.'/*.json') ?: []) : [];

        sort($shipped);
        sort($user);

        // Shipped palettes first; user palettes appended. Deduplicate by id — user wins.
        $byId = [];
        foreach ($shipped as $file) {
            $parsed = static::parseThemeFile($file);
            if ($parsed !== null) {
                $parsed['editable'] = false;
                $byId[$parsed['id']] = $parsed;
            }
        }
        foreach ($user as $file) {
            $parsed = static::parseThemeFile($file);
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
     * Retrieve the contents of a theme JSON file by searching both the bundled themes directory
     * and the user themes directory.
     *
     * @param  string  $theme  The theme filename without the `.json` extension
     * @return string|false The theme file contents if found, or false if no matching file exists
     */
    public static function getTheme(string $theme): string|false
    {
        $bundlePath = static::getBundleThemePath($theme);
        if (file_exists($bundlePath)) {
            return file_get_contents($bundlePath);
        }

        $userPath = static::getUserThemePath($theme);
        if (file_exists($userPath)) {
            return file_get_contents($userPath);
        }

        return false;
    }

    /**
     * Parse a theme JSON file and return its data as an associative array.
     * The returned array includes the theme's ID, name, description, and CSS variables for light and dark modes.
     *
     * @return array{id: string, name: string, description: string, light: array<string, string>, dark: array<string, string>}|null Note: callers are responsible for adding the `editable` key after calling this method.
     */
    public static function parseThemeFile(string $file): ?array
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
