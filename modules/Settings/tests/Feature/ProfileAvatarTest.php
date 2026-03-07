<?php

namespace Modules\Settings\Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function createVerifiedUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::USER);

        return $user;
    }

    public function test_unauthenticated_user_cannot_upload_avatar(): void
    {
        $response = $this->post(route('settings.profile.update-avatar'), [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_delete_avatar(): void
    {
        $response = $this->delete(route('settings.profile.delete-avatar'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_upload_avatar(): void
    {
        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->post(route('settings.profile.update-avatar'), [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response->assertRedirect();
        $this->assertTrue($user->fresh()->hasMedia('avatars'));
        $this->assertCount(1, $user->fresh()->getMedia('avatars'));
    }

    public function test_uploading_avatar_replaces_existing_one(): void
    {
        $user = $this->createVerifiedUser();

        $this->actingAs($user)->post(route('settings.profile.update-avatar'), [
            'avatar' => UploadedFile::fake()->image('avatar1.jpg'),
        ]);

        $this->actingAs($user)->post(route('settings.profile.update-avatar'), [
            'avatar' => UploadedFile::fake()->image('avatar2.jpg'),
        ]);

        $this->assertCount(1, $user->fresh()->getMedia('avatars'));
    }

    public function test_avatar_upload_requires_a_file(): void
    {
        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->post(route('settings.profile.update-avatar'), []);

        $response->assertInvalid('avatar');
    }

    public function test_avatar_upload_rejects_non_image_files(): void
    {
        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->post(route('settings.profile.update-avatar'), [
            'avatar' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ]);

        $response->assertInvalid('avatar');
    }

    public function test_avatar_upload_rejects_files_over_size_limit(): void
    {
        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->post(route('settings.profile.update-avatar'), [
            'avatar' => UploadedFile::fake()->image('large.jpg')->size(2049),
        ]);

        $response->assertInvalid('avatar');
    }

    public function test_authenticated_user_can_delete_avatar(): void
    {
        $user = $this->createVerifiedUser();

        $this->actingAs($user)->post(route('settings.profile.update-avatar'), [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $this->assertTrue($user->fresh()->hasMedia('avatars'));

        $this->actingAs($user)->delete(route('settings.profile.delete-avatar'));

        $this->assertFalse($user->fresh()->hasMedia('avatars'));
    }
}
