<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Every line a user reads has to reach them through the translator.
 *
 * A hardcoded string is invisible until somebody installs a language and finds half the
 * email still in English, so these tests render the notification under a fake locale whose
 * every string is replaced. Anything that comes back in English was concatenated rather
 * than translated.
 *
 * Only core notifications belong here. A module's own are covered inside that module, so
 * this suite keeps passing on an installation that does not have it.
 */
class NotificationTranslatabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The real JSON loading path, rather than Lang::addLines() — that helper routes
        // keys through Arr::set(), which splits on dots and mangles any string ending in
        // a full stop.
        Lang::addJsonPath(base_path('tests/fixtures/lang'));
    }

    public function test_password_changed_notification_translates_every_line(): void
    {
        $user = User::factory()->create(['name' => 'Ana']);

        App::setLocale('xx');

        $mail = (new PasswordChangedNotification)->toMail($user);

        // Merged, because MailMessage sorts lines into intro or outro depending on whether
        // an action came before them — and the action here is absent without the settings
        // module. Which bucket a line lands in is not what this test is about.
        $lines = [...$mail->introLines, ...$mail->outroLines];

        $this->assertSame('ALTERADA', $mail->subject);
        $this->assertContains('TROCADA', $lines);
        $this->assertContains('CONTATE', $lines);
        $this->assertContains('OBRIGADO', $lines);
    }

    public function test_the_recipient_name_is_a_placeholder_not_a_concatenation(): void
    {
        $user = User::factory()->create(['name' => 'Ana']);

        App::setLocale('xx');

        // Building the greeting with "." would leave "Hello" permanently English.
        $this->assertSame('OLA Ana,', (new PasswordChangedNotification)->toMail($user)->greeting);
    }

    /**
     * The profile button points into the settings module, which core installs without.
     */
    public function test_the_profile_button_appears_only_where_the_route_exists(): void
    {
        $user = User::factory()->create();

        App::setLocale('xx');

        if (Route::has('settings.profile')) {
            $this->assertSame('PERFIL', (new PasswordChangedNotification)->toMail($user)->actionText);

            return;
        }

        // Without the settings module the mail still sends, minus the button.
        $this->assertNull((new PasswordChangedNotification)->toMail($user)->actionText);
    }

    /**
     * `format()` hardcodes English month and meridiem names. `isoFormat()` only helps if
     * Carbon has been told which language the application is speaking.
     */
    public function test_dates_in_mail_follow_the_application_language(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2026-08-24 15:19:00');
        App::setLocale('pt_BR');

        $mail = (new PasswordChangedNotification)->toMail($user);
        $line = collect($mail->introLines)->first(fn (string $line): bool => str_contains($line, '2026'));

        $this->assertNotNull($line, 'Expected a line carrying the change time.');
        $this->assertStringContainsString('agosto', $line);
    }

    public function test_carbon_tracks_the_application_locale_in_both_directions(): void
    {
        App::setLocale('pt_BR');
        $this->assertSame('pt_BR', Carbon::getLocale());

        App::setLocale('en');
        $this->assertSame('en', Carbon::getLocale());
    }
}
