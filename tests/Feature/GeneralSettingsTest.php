<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Admin\Pages\GeneralSettings as GeneralSettingsPage;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Testing\AssertableInertia;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GeneralSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/general-settings-probe', fn () => Inertia::render('Index'));
    }

    public function test_fresh_install_has_general_settings_defaults(): void
    {
        $settings = app(GeneralSettings::class);

        $this->assertSame('Saucebase', $settings->site_name);
        $this->assertNull($settings->site_tagline);
        $this->assertNull($settings->site_description);

        // No brand images, so the logo and the favicon both stay the ones that ship.
        $this->assertNull($settings->site_icon);
        $this->assertNull($settings->site_logo);
        $this->assertFalse($settings->prefer_logo);
    }

    public function test_core_settings_migration_preserves_existing_values(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->site_name = 'Existing Platform';
        $settings->site_tagline = 'Existing tagline';
        $settings->site_description = 'Existing description.';
        $settings->save();

        $migration = require database_path('settings/0001_01_01_000010_create_general_settings.php');
        $migration->up();

        $this->assertSame('Existing Platform', $settings->site_name);
        $this->assertSame('Existing tagline', $settings->site_tagline);
        $this->assertSame('Existing description.', $settings->site_description);
    }

    public function test_administrator_can_load_general_settings_form(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $this->actingAs($admin);

        Livewire::test(GeneralSettingsPage::class)
            ->assertSchemaStateSet([
                'site_name' => 'Saucebase',
                'site_tagline' => null,
                'site_description' => null,
            ]);
    }

    public function test_administrator_can_save_general_settings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $this->actingAs($admin);

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm([
                'site_name' => 'Acme Platform',
                'site_tagline' => 'The modular SaaS starter kit',
                'site_description' => 'The Acme customer platform.',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $settings = app(GeneralSettings::class);

        $this->assertSame('Acme Platform', $settings->site_name);
        $this->assertSame('The modular SaaS starter kit', $settings->site_tagline);
        $this->assertSame('The Acme customer platform.', $settings->site_description);
    }

    public function test_site_name_is_required_without_changing_persisted_settings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $this->actingAs($admin);

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm([
                'site_name' => null,
                'site_description' => 'Changed description',
            ])
            ->call('save')
            ->assertHasFormErrors(['site_name' => 'required'])
            ->assertNotNotified();

        $settings = app(GeneralSettings::class);

        $this->assertSame('Saucebase', $settings->site_name);
        $this->assertNull($settings->site_description);
    }

    #[DataProvider('invalidSettingsProvider')]
    public function test_invalid_settings_do_not_change_persisted_values(
        string $field,
        string $value,
        string $rule,
    ): void {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $this->actingAs($admin);

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm([
                'site_name' => 'Saucebase',
                'site_description' => null,
                $field => $value,
            ])
            ->call('save')
            ->assertHasFormErrors([$field => $rule]);

        $settings = app(GeneralSettings::class);

        $this->assertSame('Saucebase', $settings->site_name);
        $this->assertNull($settings->site_description);
    }

    public function test_regular_user_cannot_access_general_settings_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::USER);

        $this->actingAs($user)
            ->get(GeneralSettingsPage::getUrl(panel: 'admin'))
            ->assertForbidden();
    }

    public function test_general_settings_are_shared_with_inertia(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->site_name = 'Acme Platform';
        $settings->site_tagline = 'The modular SaaS starter kit';
        $settings->site_description = 'The Acme customer platform.';
        $settings->save();

        $this->get('/general-settings-probe')
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->where('settings.general.site_name', 'Acme Platform')
                ->where('settings.general.site_tagline', 'The modular SaaS starter kit')
                ->where('settings.general.site_description', 'The Acme customer platform.'));
    }

    public function test_root_view_renders_settings_driven_title_and_description(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->site_name = 'Acme Platform';
        $settings->site_description = 'The Acme customer platform.';
        $settings->save();

        $content = $this->get('/general-settings-probe')->getContent();

        $this->assertSame(1, substr_count($content, '<title'));
        $this->assertStringContainsString('<title data-inertia>Acme Platform</title>', $content);
        $this->assertStringContainsString(
            '<meta data-inertia="description" name="description" content="The Acme customer platform.">',
            $content,
        );
    }

    public function test_root_view_omits_description_meta_when_no_description_is_set(): void
    {
        $content = $this->get('/general-settings-probe')->getContent();

        $this->assertStringNotContainsString('name="description"', $content);
    }

    /**
     * @return array<string, array{field: string, value: string, rule: string}>
     */
    public static function invalidSettingsProvider(): array
    {
        return [
            'site name too long' => [
                'field' => 'site_name',
                'value' => str_repeat('a', 256),
                'rule' => 'max',
            ],
            'site tagline too long' => [
                'field' => 'site_tagline',
                'value' => str_repeat('a', 61),
                'rule' => 'max',
            ],
            'site description too long' => [
                'field' => 'site_description',
                'value' => str_repeat('a', 501),
                'rule' => 'max',
            ],
        ];
    }
}
