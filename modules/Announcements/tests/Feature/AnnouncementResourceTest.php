<?php

namespace Modules\Announcements\Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Announcements\Filament\Resources\Announcements\Pages\CreateAnnouncement;
use Modules\Announcements\Filament\Resources\Announcements\Pages\EditAnnouncement;
use Modules\Announcements\Filament\Resources\Announcements\Pages\ListAnnouncements;
use Modules\Announcements\Models\Announcement;
use Tests\TestCase;

class AnnouncementResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['email_verified_at' => now()]);
        $this->admin->assignRole(Role::ADMIN);
    }

    public function test_can_list_announcements_in_filament(): void
    {
        $announcement = Announcement::factory()->active()->create();

        $this->actingAs($this->admin);

        Livewire::test(ListAnnouncements::class)
            ->assertCanSeeTableRecords([$announcement]);
    }

    public function test_can_create_announcement_and_auto_sets_created_by(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateAnnouncement::class)
            ->fillForm([
                'text' => 'Test announcement',
                'is_active' => true,
                'is_dismissable' => false,
                'show_on_frontend' => true,
                'show_on_dashboard' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('announcements', [
            'text' => '<p>Test announcement</p>',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_can_edit_an_announcement(): void
    {
        $announcement = Announcement::factory()->create();

        $this->actingAs($this->admin);

        Livewire::test(EditAnnouncement::class, ['record' => $announcement->id])
            ->fillForm(['text' => 'Updated text'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'text' => '<p>Updated text</p>',
        ]);
    }

    public function test_can_delete_an_announcement(): void
    {
        $announcement = Announcement::factory()->create();

        $this->actingAs($this->admin);

        Livewire::test(EditAnnouncement::class, ['record' => $announcement->id])
            ->callAction('delete')
            ->assertNotified();

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    public function test_active_announcement_is_shared_as_inertia_prop(): void
    {
        $announcement = Announcement::factory()->active()->create();

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertInertia(function ($page) use ($announcement) {
            $page->has('announcement')
                ->where('announcement.id', $announcement->id);
        });
    }

    public function test_inactive_announcement_is_not_shared(): void
    {
        Announcement::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertInertia(function ($page) {
            $page->where('announcement', null);
        });
    }

    public function test_dismissed_cookie_prevents_prop_from_being_shared(): void
    {
        $announcement = Announcement::factory()->active()->create();
        $cookieName = config('announcements.cookie_name');

        $response = $this->actingAs($this->admin)
            ->withCookie($cookieName, (string) $announcement->id)
            ->get('/dashboard');

        $response->assertInertia(function ($page) {
            $page->where('announcement', null);
        });
    }

    public function test_dismiss_route_sets_announcement_dismissed_cookie(): void
    {
        $announcement = Announcement::factory()->active()->create();
        $cookieName = config('announcements.cookie_name');

        $response = $this->post(route('announcements.dismiss', $announcement));

        $response->assertCookie($cookieName, (string) $announcement->id);
    }
}
