<?php

namespace Modules\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery\MockInterface;
use Tests\TestCase;

class SocialiteCallbackTest extends TestCase
{
    use RefreshDatabase;

    private function makeSocialiteUser(
        string $id = 'provider-123',
        string $email = 'socialuser@example.com',
        string $name = 'Social User',
    ): SocialiteUser {
        $socialiteUser = new SocialiteUser;
        $socialiteUser->id = $id;
        $socialiteUser->email = $email;
        $socialiteUser->name = $name;
        $socialiteUser->token = 'access-token';
        $socialiteUser->refreshToken = 'refresh-token';
        $socialiteUser->avatar = 'https://example.com/avatar.jpg';

        return $socialiteUser;
    }

    private function mockSocialiteDriver(SocialiteUser $socialiteUser): void
    {
        $abstractUser = $socialiteUser;

        Socialite::shouldReceive('driver')
            ->with('github')
            ->andReturn(
                \Mockery::mock(AbstractProvider::class, function (MockInterface $mock) use ($abstractUser) {
                    $mock->shouldReceive('user')->andReturn($abstractUser);
                }),
            );
    }

    public function test_callback_sets_last_social_provider_cookie(): void
    {
        $socialiteUser = $this->makeSocialiteUser();
        $this->mockSocialiteDriver($socialiteUser);

        $response = $this->get(route('auth.socialite.callback', ['provider' => 'github']));

        $response->assertRedirect();
        $response->assertCookie('last_social_provider', 'github');
    }

    public function test_callback_does_not_set_cookie_during_account_linking(): void
    {
        $user = $this->createUser();
        $socialiteUser = $this->makeSocialiteUser(email: $user->email);
        $this->mockSocialiteDriver($socialiteUser);

        $response = $this->actingAs($user)
            ->get(route('auth.socialite.callback', ['provider' => 'github']));

        $response->assertRedirect();
        $response->assertCookieMissing('last_social_provider');
    }
}
