<?php

namespace Modules\Roadmap\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Roadmap\Enums\RoadmapStatus;
use Modules\Roadmap\Enums\RoadmapType;
use Modules\Roadmap\Enums\VoteType;
use Modules\Roadmap\Models\RoadmapItem;
use Modules\Roadmap\Models\RoadmapVote;
use Tests\TestCase;

class RoadmapItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_scope_excludes_pending_approval_items(): void
    {
        RoadmapItem::factory()->create(['status' => RoadmapStatus::PendingApproval]);

        $results = RoadmapItem::public()->get();

        $this->assertCount(0, $results);
    }

    public function test_public_scope_excludes_rejected_items(): void
    {
        RoadmapItem::factory()->create(['status' => RoadmapStatus::Rejected]);

        $results = RoadmapItem::public()->get();

        $this->assertCount(0, $results);
    }

    public function test_public_scope_excludes_cancelled_items(): void
    {
        RoadmapItem::factory()->create(['status' => RoadmapStatus::Cancelled]);

        $results = RoadmapItem::public()->get();

        $this->assertCount(0, $results);
    }

    public function test_public_scope_includes_approved_items(): void
    {
        $item = RoadmapItem::factory()->approved()->create();

        $results = RoadmapItem::public()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($item->id, $results->first()->id);
    }

    public function test_public_scope_includes_in_progress_items(): void
    {
        $item = RoadmapItem::factory()->inProgress()->create();

        $results = RoadmapItem::public()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($item->id, $results->first()->id);
    }

    public function test_public_scope_includes_completed_items(): void
    {
        $item = RoadmapItem::factory()->completed()->create();

        $results = RoadmapItem::public()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($item->id, $results->first()->id);
    }

    public function test_public_scope_loads_upvotes_and_downvotes_count(): void
    {
        $item = RoadmapItem::factory()->approved()->create();

        $voters = User::factory()->count(3)->create();
        foreach ($voters as $voter) {
            RoadmapVote::create(['roadmap_item_id' => $item->id, 'user_id' => $voter->id, 'type' => VoteType::Up]);
        }
        RoadmapVote::create(['roadmap_item_id' => $item->id, 'user_id' => User::factory()->create()->id, 'type' => VoteType::Down]);

        $result = RoadmapItem::public()->first();

        $this->assertEquals(3, $result->upvotes_count);
        $this->assertEquals(1, $result->downvotes_count);
    }

    public function test_status_casts_to_enum(): void
    {
        $item = RoadmapItem::factory()->create(['status' => RoadmapStatus::Approved]);

        $this->assertInstanceOf(RoadmapStatus::class, $item->fresh()->status);
        $this->assertEquals(RoadmapStatus::Approved, $item->fresh()->status);
    }

    public function test_type_casts_to_enum(): void
    {
        $item = RoadmapItem::factory()->feature()->create();

        $this->assertInstanceOf(RoadmapType::class, $item->fresh()->type);
        $this->assertEquals(RoadmapType::Feature, $item->fresh()->type);
    }
}
