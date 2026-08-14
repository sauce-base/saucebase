<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Admin\Pages\GeneralSettings as GeneralSettingsPage;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
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

        app()->forgetInstance(GeneralSettings::class);
        $reloadedSettings = app(GeneralSettings::class);

        $this->assertSame('Existing Platform', $reloadedSettings->site_name);
        $this->assertSame('Existing tagline', $reloadedSettings->site_tagline);
        $this->assertSame('Existing description.', $reloadedSettings->site_description);
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
                'site_icon' => null,
                'site_logo' => null,
                'prefer_logo' => false,
            ]);
    }

    public function test_administrator_can_save_general_settings(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $this->actingAs($admin);

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm([
                'site_name' => 'Acme Platform',
                'site_tagline' => 'The modular SaaS starter kit',
                'site_description' => 'The Acme customer platform.',
                'site_icon' => UploadedFile::fake()->image('icon.png', 512, 512),
                'site_logo' => UploadedFile::fake()->image('logo.png', 1200, 400),
                'prefer_logo' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        app()->forgetInstance(GeneralSettings::class);
        $settings = app(GeneralSettings::class);

        $this->assertSame('Acme Platform', $settings->site_name);
        $this->assertSame('The modular SaaS starter kit', $settings->site_tagline);
        $this->assertSame('The Acme customer platform.', $settings->site_description);
        $this->assertStringStartsWith('site-branding/', $settings->site_icon);
        $this->assertStringStartsWith('site-branding/', $settings->site_logo);
        $this->assertTrue($settings->prefer_logo);
        Storage::disk('public')->assertExists($settings->site_icon);
        Storage::disk('public')->assertExists($settings->site_logo);
        $this->assertSame(Storage::disk('public')->url($settings->site_icon), $settings->siteIconUrl());
        $this->assertSame(Storage::disk('public')->url($settings->site_logo), $settings->siteLogoUrl());
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

    #[DataProvider('brandImageFieldsProvider')]
    public function test_branding_uploads_must_be_images(string $field): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $this->actingAs($admin);

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm([
                'site_name' => 'Saucebase',
                $field => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            ])
            ->call('save')
            ->assertHasFormErrors([$field])
            ->assertNotNotified();
    }

    #[DataProvider('brandImageFieldsProvider')]
    public function test_branding_uploads_must_not_exceed_one_megabyte(string $field): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $this->actingAs($admin);

        Livewire::test(GeneralSettingsPage::class)
            ->fillForm([
                'site_name' => 'Saucebase',
                $field => UploadedFile::fake()->image('large.png')->size(1025),
            ])
            ->call('save')
            ->assertHasFormErrors([$field])
            ->assertNotNotified();
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
        Storage::fake('public');
        Storage::disk('public')->put('site-branding/icon.png', 'icon');

        $settings = app(GeneralSettings::class);
        $settings->site_name = 'Acme Platform';
        $settings->site_tagline = 'The modular SaaS starter kit';
        $settings->site_description = 'The Acme customer platform.';
        $settings->site_icon = 'site-branding/icon.png';
        $settings->site_logo = 'https://cdn.example.com/logo.svg';
        $settings->save();

        $this->get('/general-settings-probe')
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->where('settings.general.site_name', 'Acme Platform')
                ->where('settings.general.site_tagline', 'The modular SaaS starter kit')
                ->where('settings.general.site_description', 'The Acme customer platform.')
                ->where('settings.general.site_icon', Storage::disk('public')->url('site-branding/icon.png'))
                ->where('settings.general.site_logo', 'https://cdn.example.com/logo.svg'));
    }

    public function test_frontend_stack_views_render_settings_driven_metadata(): void
    {
        foreach (['vue', 'react'] as $stack) {
            $template = file_get_contents(base_path("stubs/saucebase/stack/{$stack}/views/app.blade.php"));

            $this->assertIsString($template);
            $this->assertStringContainsString(
                '<title data-inertia>{{ $generalSettings->site_name }}</title>',
                $template,
            );
            $this->assertMatchesRegularExpression(
                '/@if \(\$generalSettings->site_description\).*name="description".*@endif/s',
                $template,
            );
        }
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

    /**
     * @return array<string, array{field: string}>
     */
    public static function brandImageFieldsProvider(): array
    {
        return [
            'site icon' => ['field' => 'site_icon'],
            'site logo' => ['field' => 'site_logo'],
        ];
    }
}
