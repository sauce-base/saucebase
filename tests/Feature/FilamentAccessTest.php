<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class FilamentAccessTest extends TestCase
{
    use RefreshDatabase;

    private Panel $panel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->panel = Mockery::mock(Panel::class);
    }

    public function test_admin_user_can_access_filament_panel(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $this->assertTrue($admin->canAccessPanel($this->panel));
    }

    public function test_regular_user_cannot_access_filament_panel(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::USER);

        $this->assertFalse($user->canAccessPanel($this->panel));
    }

    public function test_user_with_no_role_cannot_access_filament_panel(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->assertFalse($user->canAccessPanel($this->panel));
    }
}
