<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Modules\Auth\Notifications\WelcomeNotification;
use Tests\TestCase;

/**
 * Every line a user reads has to reach them through the translator.
 *
 * A hardcoded string is invisible until somebody installs a language and finds half the
 * email still in English, so these tests render each notification under a fake locale
 * whose every string is replaced. Anything that comes back in English was concatenated
 * rather than translated.
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

        $this->assertSame('ALTERADA', $mail->subject);
        $this->assertSame('PERFIL', $mail->actionText);
        $this->assertContains('TROCADA', $mail->introLines);
        $this->assertContains('CONTATE', $mail->introLines);
        $this->assertContains('OBRIGADO', $mail->outroLines);
    }

    public function test_welcome_notification_translates_every_line(): void
    {
        $user = User::factory()->create(['name' => 'Ana']);

        App::setLocale('xx');

        $mail = (new WelcomeNotification)->toMail($user);

        $this->assertStringStartsWith('BEMVINDO', $mail->subject);
        $this->assertSame('PAINEL', $mail->actionText);
        $this->assertContains('CRIADA', $mail->introLines);
        $this->assertContains('EXPLORE', $mail->introLines);
        $this->assertContains('GRATO', $mail->outroLines);
    }

    public function test_the_recipient_name_is_a_placeholder_not_a_concatenation(): void
    {
        $user = User::factory()->create(['name' => 'Ana']);

        App::setLocale('xx');

        // Building the greeting with "." would leave "Hello" permanently English.
        $this->assertSame('OLA Ana,', (new PasswordChangedNotification)->toMail($user)->greeting);
        $this->assertSame('OLA Ana,', (new WelcomeNotification)->toMail($user)->greeting);
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
