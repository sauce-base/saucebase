<?php

namespace Modules\Auth\Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_sees_verification_prompt(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)
            ->get(route('verification.notice'));

        $response->assertStatus(200);
    }

    public function test_verified_user_is_redirected_from_prompt(): void
    {
        $user = User::factory()->create(); // email_verified_at is set by default

        $response = $this->actingAs($user)
            ->get(route('verification.notice'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_email_is_verified_via_valid_signed_url(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_invalid_signed_url_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $unsignedUrl = route('verification.verify', [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $response = $this->actingAs($user)->get($unsignedUrl);

        $response->assertStatus(403);
    }

    public function test_verification_notification_can_be_resent(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post(route('verification.send'));

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
