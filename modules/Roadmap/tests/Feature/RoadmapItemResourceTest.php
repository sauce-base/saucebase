<?php

namespace Modules\Roadmap\Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Roadmap\Enums\RoadmapStatus;
use Modules\Roadmap\Enums\RoadmapType;
use Modules\Roadmap\Filament\Resources\Roadmap\Pages\CreateRoadmapItem;
use Modules\Roadmap\Filament\Resources\Roadmap\Pages\EditRoadmapItem;
use Modules\Roadmap\Filament\Resources\Roadmap\Pages\ListRoadmapItems;
use Modules\Roadmap\Models\RoadmapItem;
use Tests\TestCase;

class RoadmapItemResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['email_verified_at' => now()]);
        $this->admin->assignRole(Role::ADMIN);
    }

    public function test_admin_can_list_roadmap_items(): void
    {
        $item = RoadmapItem::factory()->approved()->create();

        $this->actingAs($this->admin);

        Livewire::test(ListRoadmapItems::class)
            ->assertCanSeeTableRecords([$item]);
    }

    public function test_admin_can_create_roadmap_item(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateRoadmapItem::class)
            ->fillForm([
                'title' => 'New feature from admin',
                'slug' => 'new-feature-from-admin',
                'status' => RoadmapStatus::Approved->value,
                'type' => RoadmapType::Feature->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('roadmap_items', [
            'title' => 'New feature from admin',
            'status' => RoadmapStatus::Approved->value,
        ]);
    }

    public function test_admin_can_edit_roadmap_item_status(): void
    {
        $item = RoadmapItem::factory()->create(['status' => RoadmapStatus::PendingApproval]);

        $this->actingAs($this->admin);

        Livewire::test(EditRoadmapItem::class, ['record' => $item->id])
            ->fillForm(['status' => RoadmapStatus::Approved->value])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('roadmap_items', [
            'id' => $item->id,
            'status' => RoadmapStatus::Approved->value,
        ]);
    }

    public function test_admin_can_delete_roadmap_item(): void
    {
        $item = RoadmapItem::factory()->create();

        $this->actingAs($this->admin);

        Livewire::test(EditRoadmapItem::class, ['record' => $item->id])
            ->callAction('delete')
            ->assertNotified();

        $this->assertDatabaseMissing('roadmap_items', ['id' => $item->id]);
    }

    public function test_pending_items_are_visible_in_admin_list(): void
    {
        $pending = RoadmapItem::factory()->create(['status' => RoadmapStatus::PendingApproval]);

        $this->actingAs($this->admin);

        Livewire::test(ListRoadmapItems::class)
            ->assertCanSeeTableRecords([$pending]);
    }
}
