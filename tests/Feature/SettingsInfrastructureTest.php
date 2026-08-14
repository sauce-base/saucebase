<?php

namespace Tests\Feature;

use App\Filament\Pages\SettingsPage;
use Filament\Pages\SettingsPage as FilamentSettingsPage;
use Filament\SpatieLaravelSettingsPluginServiceProvider;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Tests\TestCase;

class SettingsInfrastructureTest extends TestCase
{
    /**
     * Modules ship settings pages without requiring the plugin themselves, so root has to
     * carry it. Presence is asserted, not the constraint: which version satisfies this is
     * `composer.json`'s business, and pinning it here only breaks the test on a bump it
     * has nothing to say about.
     */
    public function test_root_application_provides_settings_infrastructure_to_modules(): void
    {
        $rootComposer = json_decode(
            file_get_contents(base_path('composer.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertArrayHasKey(
            'filament/spatie-laravel-settings-plugin',
            $rootComposer['require'],
            'The settings plugin must be required by the root application, not by a module.',
        );

        $this->assertTrue(
            class_exists(SpatieLaravelSettingsPluginServiceProvider::class),
            'The settings plugin is declared but not installed.',
        );
    }

    public function test_root_application_migrations_create_the_settings_repository(): void
    {
        $this->artisan('migrate:fresh', [
            '--path' => 'database/migrations',
            '--no-interaction' => true,
        ])->assertSuccessful();

        $settingsTable = config('settings.repositories.database.table') ?? 'settings';

        $this->assertTrue(Schema::hasTable($settingsTable));
    }

    /**
     * The base class exists so the constraint is set once rather than remembered three
     * times. Without a width every settings page fills the viewport, which stretches a
     * single-column form across a wide monitor.
     */
    public function test_the_shared_settings_page_constrains_its_width(): void
    {
        $width = (new ReflectionClass(SettingsPage::class))
            ->getDefaultProperties()['maxContentWidth'] ?? null;

        $this->assertInstanceOf(Width::class, $width);
        $this->assertNotSame(Width::Full, $width);
    }

    public function test_settings_navigation_sort_is_bounded_at_the_integer_limit(): void
    {
        $navigationSort = new ReflectionClass(SettingsPage::class)->getProperty('navigationSort');
        $originalNavigationSort = $navigationSort->getValue();

        try {
            $navigationSort->setValue(null, 1000);
            $this->assertSame(PHP_INT_MAX, SettingsPage::getNavigationSort());

            $navigationSort->setValue(null, 1001);
            $this->assertSame(PHP_INT_MAX, SettingsPage::getNavigationSort());
        } finally {
            $navigationSort->setValue(null, $originalNavigationSort);
        }
    }

    /**
     * Discovered rather than listed: a settings page added by a future module has to opt
     * into the convention, and the only way this test notices is by finding it.
     */
    public function test_every_module_settings_page_extends_the_shared_base(): void
    {
        $pages = $this->moduleSettingsPages();

        $invalidPages = array_values(array_filter(
            $pages,
            fn (string $page): bool => ! is_subclass_of($page, SettingsPage::class),
        ));

        $this->assertSame(
            [],
            $invalidPages,
            implode(', ', $invalidPages).' must extend '.SettingsPage::class.'.',
        );
    }

    /**
     * @return array<int, class-string>
     */
    private function moduleSettingsPages(): array
    {
        $pages = [];

        foreach (glob(base_path('modules/*/src/Filament/Pages/*.php')) ?: [] as $file) {
            $module = basename(dirname($file, 4));
            $class = 'Modules\\'.str($module)->studly().'\\Filament\\Pages\\'.basename($file, '.php');

            if (class_exists($class) && is_subclass_of($class, FilamentSettingsPage::class)) {
                $pages[] = $class;
            }
        }

        return $pages;
    }
}
