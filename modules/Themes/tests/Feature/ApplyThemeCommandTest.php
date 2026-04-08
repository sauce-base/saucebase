<?php

namespace Modules\Themes\Tests\Feature;

use Tests\TestCase;

class ApplyThemeCommandTest extends TestCase
{
    private string $themesDir;

    private string $themeCssPath;

    private string $originalCss;

    protected function setUp(): void
    {
        parent::setUp();

        $this->themesDir = module_path('Themes', 'resources/themes');
        $this->themeCssPath = resource_path('css/theme.css');
        $this->originalCss = file_get_contents($this->themeCssPath);
    }

    protected function tearDown(): void
    {
        // Restore theme.css after each test
        file_put_contents($this->themeCssPath, $this->originalCss);

        parent::tearDown();
    }

    // ── New shadcn format ─────────────────────────────────────────────────────

    public function test_new_format_patches_light_vars_into_root_block(): void
    {
        $this->artisan('saucebase:theme:apply default')->assertSuccessful();

        $css = file_get_contents($this->themeCssPath);
        // Vars from default.json light section should be in :root
        $this->assertStringContainsString('--primary:', $css);
        $this->assertStringContainsString('--background:', $css);
    }

    public function test_new_format_patches_dark_vars_into_dark_block(): void
    {
        $this->artisan('saucebase:theme:apply default')->assertSuccessful();

        $css = file_get_contents($this->themeCssPath);
        // After the :root block, .dark block should also be updated
        $darkBlock = substr($css, strpos($css, '.dark'));
        $this->assertStringContainsString('--primary:', $darkBlock);
        $this->assertStringContainsString('--background:', $darkBlock);
    }

    public function test_new_format_theme_vars_are_applied_to_both_blocks(): void
    {
        $this->artisan('saucebase:theme:apply default')->assertSuccessful();

        $css = file_get_contents($this->themeCssPath);

        $rootEnd = strpos($css, '.dark');
        $rootBlock = substr($css, 0, $rootEnd);
        $darkBlock = substr($css, $rootEnd);

        // font-sans comes from cssVars.theme and should appear in both blocks
        $this->assertStringContainsString('--font-sans:', $rootBlock);
        $this->assertStringContainsString('--font-sans:', $darkBlock);
    }

    public function test_new_format_uses_double_dash_prefix_in_css(): void
    {
        $this->artisan('saucebase:theme:apply default')->assertSuccessful();

        $css = file_get_contents($this->themeCssPath);
        // Keys in JSON have no '--' prefix; command must add it
        $this->assertStringContainsString('--primary:', $css);
        $this->assertStringNotContainsString("\n    primary:", $css);
    }

    public function test_shadow_offset_vars_are_applied_correctly(): void
    {
        $this->artisan('saucebase:theme:apply default')->assertSuccessful();

        $css = file_get_contents($this->themeCssPath);
        // Renamed vars (shadow-offset-x/y, not shadow-x/y)
        $this->assertStringContainsString('--shadow-offset-x:', $css);
        $this->assertStringContainsString('--shadow-offset-y:', $css);
    }

    // ── @layer base ───────────────────────────────────────────────────────────

    public function test_layer_base_rules_are_patched_into_css(): void
    {
        $this->artisan('saucebase:theme:apply default')->assertSuccessful();

        $css = file_get_contents($this->themeCssPath);
        $this->assertStringContainsString('@layer base', $css);
        $this->assertMatchesRegularExpression('/body\s*\{[^}]*letter-spacing\s*:/s', $css);
    }

    // ── Error handling ────────────────────────────────────────────────────────

    public function test_missing_theme_returns_failure(): void
    {
        $this->artisan('saucebase:theme:apply non-existent-theme-xyz')->assertFailed();
    }

    public function test_theme_css_is_unchanged_on_failure(): void
    {
        $cssBefore = file_get_contents($this->themeCssPath);

        $this->artisan('saucebase:theme:apply non-existent-theme-xyz')->assertFailed();

        $cssAfter = file_get_contents($this->themeCssPath);
        $this->assertSame($cssBefore, $cssAfter);
    }
}
