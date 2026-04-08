<?php

namespace Modules\Themes\Tests\Unit;

use Modules\Themes\Providers\ThemesServiceProvider;
use ReflectionMethod;
use Tests\TestCase;

class ParseThemeFileTest extends TestCase
{
    private function parse(string $file): ?array
    {
        $provider = new ThemesServiceProvider(app());
        $method = new ReflectionMethod($provider, 'parseThemeFile');

        return $method->invoke($provider, $file);
    }

    private function writeTempTheme(array $data): string
    {
        $path = tempnam(sys_get_temp_dir(), 'theme_').'.json';
        file_put_contents($path, json_encode($data));

        return $path;
    }

    // ── New shadcn format ─────────────────────────────────────────────────────

    public function test_new_format_returns_correct_id_and_name(): void
    {
        $file = $this->writeTempTheme([
            'name' => 'my-theme',
            'title' => 'My Theme',
            'description' => 'A test theme',
            'cssVars' => [
                'theme' => [],
                'light' => ['primary' => 'red'],
                'dark' => ['primary' => 'blue'],
            ],
        ]);

        $result = $this->parse($file);

        $this->assertNotNull($result);
        $this->assertSame('my-theme', $result['id']);
        $this->assertSame('My Theme', $result['name']);
        $this->assertSame('A test theme', $result['description']);

        unlink($file);
    }

    public function test_new_format_adds_double_dash_prefix_to_keys(): void
    {
        $file = $this->writeTempTheme([
            'name' => 'test',
            'title' => 'Test',
            'cssVars' => [
                'theme' => [],
                'light' => ['primary' => 'oklch(0.5 0.2 200)', 'background' => 'oklch(1 0 0)'],
                'dark' => ['primary' => 'oklch(0.4 0.2 200)', 'background' => 'oklch(0.2 0 0)'],
            ],
        ]);

        $result = $this->parse($file);

        $this->assertNotNull($result);
        $this->assertArrayHasKey('--primary', $result['light']);
        $this->assertArrayHasKey('--background', $result['light']);
        $this->assertArrayNotHasKey('primary', $result['light']);
        $this->assertArrayHasKey('--primary', $result['dark']);

        unlink($file);
    }

    public function test_new_format_merges_theme_vars_into_both_light_and_dark(): void
    {
        $file = $this->writeTempTheme([
            'name' => 'test',
            'title' => 'Test',
            'cssVars' => [
                'theme' => ['font-sans' => 'Inter, sans-serif', 'radius' => '0.5rem'],
                'light' => ['primary' => 'red'],
                'dark' => ['primary' => 'blue'],
            ],
        ]);

        $result = $this->parse($file);

        $this->assertNotNull($result);
        // Theme vars merged into light
        $this->assertArrayHasKey('--font-sans', $result['light']);
        $this->assertArrayHasKey('--radius', $result['light']);
        $this->assertSame('Inter, sans-serif', $result['light']['--font-sans']);
        // Theme vars merged into dark
        $this->assertArrayHasKey('--font-sans', $result['dark']);
        $this->assertArrayHasKey('--radius', $result['dark']);
        // Mode-specific vars still present
        $this->assertSame('red', $result['light']['--primary']);
        $this->assertSame('blue', $result['dark']['--primary']);

        unlink($file);
    }

    public function test_new_format_mode_vars_override_theme_vars(): void
    {
        $file = $this->writeTempTheme([
            'name' => 'test',
            'title' => 'Test',
            'cssVars' => [
                'theme' => ['radius' => '0.5rem'],
                'light' => ['radius' => '1rem'],
                'dark' => [],
            ],
        ]);

        $result = $this->parse($file);

        $this->assertNotNull($result);
        // Light-specific radius overrides theme radius
        $this->assertSame('1rem', $result['light']['--radius']);
        // Dark uses theme radius (no dark override)
        $this->assertSame('0.5rem', $result['dark']['--radius']);

        unlink($file);
    }

    public function test_new_format_falls_back_title_to_filename(): void
    {
        $path = sys_get_temp_dir().'/mythemefile.json';
        file_put_contents($path, json_encode([
            'name' => 'mythemefile',
            'cssVars' => ['theme' => [], 'light' => [], 'dark' => []],
        ]));

        $result = $this->parse($path);

        $this->assertNotNull($result);
        $this->assertSame('Mythemefile', $result['name']);

        unlink($path);
    }

    public function test_missing_name_returns_null(): void
    {
        $file = $this->writeTempTheme([
            'title' => 'No Name',
            'cssVars' => ['theme' => [], 'light' => [], 'dark' => []],
        ]);

        $result = $this->parse($file);

        $this->assertNull($result);

        unlink($file);
    }

    public function test_missing_css_vars_returns_null(): void
    {
        $file = $this->writeTempTheme([
            'name' => 'no-css-vars',
            'title' => 'No CSS Vars',
        ]);

        $result = $this->parse($file);

        $this->assertNull($result);

        unlink($file);
    }

    // ── Invalid inputs ────────────────────────────────────────────────────────

    public function test_non_existent_file_returns_null(): void
    {
        $result = $this->parse('/tmp/this-file-does-not-exist-12345.json');

        $this->assertNull($result);
    }

    public function test_invalid_json_returns_null(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'theme_').'.json';
        file_put_contents($path, 'not valid json {{{');

        $result = $this->parse($path);

        $this->assertNull($result);

        unlink($path);
    }
}
