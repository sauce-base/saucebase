<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Admin\Pages\LocalizationSettings as LocalizationSettingsPage;
use App\Models\User;
use App\Settings\LocalizationSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Testing\AssertableInertia;
use Livewire\Livewire;
use Tests\TestCase;

class LocalizationSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/localization-probe', fn () => Inertia::render('Index'));
    }

    public function test_fresh_install_offers_the_locales_that_ship(): void
    {
        $settings = app(LocalizationSettings::class);

        $this->assertSame(['en', 'pt_BR'], $settings->enabled_locales);
        $this->assertSame('en', $settings->default_locale);
    }

    public function test_settings_migration_preserves_existing_values(): void
    {
        $this->setEnabledLocales(['en'], 'en');

        $migration = require database_path('settings/0001_01_01_000012_create_localization_settings.php');
        $migration->up();

        app()->forgetInstance(LocalizationSettings::class);
        $settings = app(LocalizationSettings::class);

        $this->assertSame(['en'], $settings->enabled_locales);
    }

    public function test_available_locales_are_discovered_from_lang_directories(): void
    {
        $available = app(LocalizationSettings::class)->available();

        // Both ship in core; pt_BR also ships inside several modules, which must not
        // produce a duplicate.
        $this->assertSame(['en', 'pt_BR'], array_keys($available));

        // Named from config, so the selector shows the endonym rather than an intl string
        // like "Portuguese (Brazil)".
        $this->assertSame('Português', $available['pt_BR']);
    }

    public function test_disabling_a_locale_removes_it_from_the_shared_prop(): void
    {
        $this->setEnabledLocales(['en'], 'en');

        $this->get('/localization-probe')->assertInertia(
            fn (AssertableInertia $page) => $page->where('locales', ['en' => 'English'])
        );
    }

    public function test_default_locale_applies_when_the_visitor_has_not_chosen(): void
    {
        $this->setEnabledLocales(['en', 'pt_BR'], 'pt_BR');

        $this->get('/localization-probe')->assertOk();

        $this->assertSame('pt_BR', app()->getLocale());
    }

    public function test_a_session_locale_that_is_no_longer_enabled_is_ignored(): void
    {
        $this->setEnabledLocales(['en'], 'en');

        $this->withSession(['locale' => 'pt_BR'])->get('/localization-probe')->assertOk();

        $this->assertSame('en', app()->getLocale());
    }

    public function test_switching_to_an_enabled_locale_is_accepted(): void
    {
        $this->setEnabledLocales(['en', 'pt_BR'], 'en');

        $this->post('/locale/pt_BR')
            ->assertOk()
            ->assertJson(['locale' => 'pt_BR']);

        $this->assertSame('pt_BR', session('locale'));
    }

    public function test_switching_to_a_disabled_locale_is_rejected(): void
    {
        $this->setEnabledLocales(['en'], 'en');

        $this->post('/locale/pt_BR')
            ->assertStatus(400)
            ->assertJson(['error' => 'Invalid locale']);

        $this->assertNull(session('locale'));
    }

    public function test_switching_stores_the_language_on_the_signed_in_user(): void
    {
        $this->setEnabledLocales(['en', 'pt_BR'], 'en');

        $user = User::factory()->create();

        $this->actingAs($user)->post('/locale/pt_BR')->assertOk();

        $this->assertSame('pt_BR', $user->fresh()->locale);
    }

    public function test_a_stored_user_language_survives_a_new_session(): void
    {
        $this->setEnabledLocales(['en', 'pt_BR'], 'en');

        $user = User::factory()->create(['locale' => 'pt_BR']);

        // No session locale at all: a different browser, or a session that has expired.
        $this->actingAs($user)->get('/localization-probe')->assertOk();

        $this->assertSame('pt_BR', app()->getLocale());
    }

    public function test_a_stored_user_language_outranks_the_session(): void
    {
        $this->setEnabledLocales(['en', 'pt_BR'], 'en');

        $user = User::factory()->create(['locale' => 'pt_BR']);

        $this->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get('/localization-probe')
            ->assertOk();

        $this->assertSame('pt_BR', app()->getLocale());
    }

    public function test_a_stored_user_language_that_is_no_longer_enabled_falls_back(): void
    {
        $user = User::factory()->create(['locale' => 'pt_BR']);

        $this->setEnabledLocales(['en'], 'en');

        $this->actingAs($user)->get('/localization-probe')->assertOk();

        $this->assertSame('en', app()->getLocale());
    }

    public function test_a_user_language_drives_notifications(): void
    {
        $this->setEnabledLocales(['en', 'pt_BR'], 'en');

        $user = User::factory()->create(['locale' => 'pt_BR']);

        // The contract Laravel reads when sending, which is what carries the choice
        // beyond the request it was made in.
        $this->assertSame('pt_BR', $user->preferredLocale());
    }

    public function test_a_user_who_never_chose_expresses_no_preference(): void
    {
        $user = User::factory()->create(['locale' => null]);

        // Null leaves withLocale() alone, so a notification sent mid-request stays in the
        // language the visitor is actually reading.
        $this->assertNull($user->preferredLocale());
    }

    public function test_a_user_language_that_is_no_longer_enabled_expresses_no_preference(): void
    {
        $user = User::factory()->create(['locale' => 'pt_BR']);

        $this->setEnabledLocales(['en'], 'en');

        // Otherwise the browser would say English and the email would still say Portuguese.
        $this->assertNull($user->fresh()->preferredLocale());
    }

    public function test_enabled_never_resolves_to_no_language(): void
    {
        $this->setEnabledLocales([], 'en');

        // An empty setting would otherwise leave the application rendering raw keys.
        $this->assertSame(['en' => 'English'], app(LocalizationSettings::class)->enabled());
    }

    public function test_a_default_locale_that_is_no_longer_enabled_falls_back(): void
    {
        $this->setEnabledLocales(['pt_BR'], 'en');

        $this->get('/localization-probe')->assertOk();

        $this->assertSame('pt_BR', app()->getLocale());
    }

    public function test_administrator_can_save_localization_settings(): void
    {
        $this->actingAsAdmin();

        Livewire::test(LocalizationSettingsPage::class)
            ->assertSchemaStateSet([
                'enabled_locales' => ['en', 'pt_BR'],
                'default_locale' => 'en',
            ])
            ->fillForm([
                'enabled_locales' => ['pt_BR'],
                'default_locale' => 'pt_BR',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        app()->forgetInstance(LocalizationSettings::class);
        $settings = app(LocalizationSettings::class);

        $this->assertSame(['pt_BR'], $settings->enabled_locales);
        $this->assertSame('pt_BR', $settings->default_locale);
    }

    public function test_at_least_one_language_must_stay_enabled(): void
    {
        $this->actingAsAdmin();

        Livewire::test(LocalizationSettingsPage::class)
            ->fillForm([
                'enabled_locales' => [],
                'default_locale' => 'en',
            ])
            ->call('save')
            ->assertHasFormErrors(['enabled_locales']);

        app()->forgetInstance(LocalizationSettings::class);

        $this->assertSame(['en', 'pt_BR'], app(LocalizationSettings::class)->enabled_locales);
    }

    /**
     * @param  list<string>  $locales
     */
    private function setEnabledLocales(array $locales, string $default): void
    {
        $settings = app(LocalizationSettings::class);
        $settings->enabled_locales = $locales;
        $settings->default_locale = $default;
        $settings->save();

        app()->forgetInstance(LocalizationSettings::class);
    }

    private function actingAsAdmin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $this->actingAs($admin);
    }
}
