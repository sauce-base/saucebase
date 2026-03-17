<?php

namespace Modules\Roadmap\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Roadmap\Enums\RoadmapStatus;
use Modules\Roadmap\Models\RoadmapItem;
use Tests\TestCase;

class RoadmapVoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_vote_and_is_redirected(): void
    {
        $item = RoadmapItem::factory()->approved()->create();

        $response = $this->post(route('roadmap.vote', $item));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('roadmap_votes', 0);
    }

    public function test_authenticated_user_can_upvote(): void
    {
        $user = User::factory()->create();
        $item = RoadmapItem::factory()->approved()->create();

        $this->actingAs($user)->post(route('roadmap.vote', $item), ['type' => 'up']);

        $this->assertDatabaseHas('roadmap_votes', [
            'roadmap_item_id' => $item->id,
            'user_id' => $user->id,
            'type' => 'up',
        ]);
    }

    public function test_user_can_downvote(): void
    {
        $user = User::factory()->create();
        $item = RoadmapItem::factory()->approved()->create();

        $this->actingAs($user)->post(route('roadmap.vote', $item), ['type' => 'down']);

        $this->assertDatabaseHas('roadmap_votes', [
            'roadmap_item_id' => $item->id,
            'user_id' => $user->id,
            'type' => 'down',
        ]);
    }

    public function test_voting_same_direction_removes_the_vote(): void
    {
        $user = User::factory()->create();
        $item = RoadmapItem::factory()->approved()->create();

        $this->actingAs($user)->post(route('roadmap.vote', $item), ['type' => 'up']);
        $this->assertDatabaseCount('roadmap_votes', 1);

        $this->actingAs($user)->post(route('roadmap.vote', $item), ['type' => 'up']);
        $this->assertDatabaseCount('roadmap_votes', 0);
    }

    public function test_downvote_again_removes_vote(): void
    {
        $user = User::factory()->create();
        $item = RoadmapItem::factory()->approved()->create();

        $this->actingAs($user)->post(route('roadmap.vote', $item), ['type' => 'down']);
        $this->actingAs($user)->post(route('roadmap.vote', $item), ['type' => 'down']);

        $this->assertDatabaseCount('roadmap_votes', 0);
    }

    public function test_switching_from_upvote_to_downvote(): void
    {
        $user = User::factory()->create();
        $item = RoadmapItem::factory()->approved()->create();

        $this->actingAs($user)->post(route('roadmap.vote', $item), ['type' => 'up']);
        $this->actingAs($user)->post(route('roadmap.vote', $item), ['type' => 'down']);

        $this->assertDatabaseCount('roadmap_votes', 1);
        $this->assertDatabaseHas('roadmap_votes', ['user_id' => $user->id, 'type' => 'down']);
    }

    public function test_switching_from_downvote_to_upvote(): void
    {
        $user = User::factory()->create();
        $item = RoadmapItem::factory()->approved()->create();

        $this->actingAs($user)->post(route('roadmap.vote', $item), ['type' => 'down']);
        $this->actingAs($user)->post(route('roadmap.vote', $item), ['type' => 'up']);

        $this->assertDatabaseCount('roadmap_votes', 1);
        $this->assertDatabaseHas('roadmap_votes', ['user_id' => $user->id, 'type' => 'up']);
    }

    public function test_multiple_users_can_vote_on_the_same_item(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $item = RoadmapItem::factory()->approved()->create();

        $this->actingAs($userA)->post(route('roadmap.vote', $item), ['type' => 'up']);
        $this->actingAs($userB)->post(route('roadmap.vote', $item), ['type' => 'up']);

        $this->assertDatabaseCount('roadmap_votes', 2);
    }

    public function test_cannot_vote_on_pending_approval_item(): void
    {
        $user = User::factory()->create();
        $item = RoadmapItem::factory()->create(['status' => RoadmapStatus::PendingApproval]);

        $response = $this->actingAs($user)->post(route('roadmap.vote', $item), ['type' => 'up']);

        $response->assertNotFound();
        $this->assertDatabaseCount('roadmap_votes', 0);
    }

    public function test_cannot_vote_on_rejected_item(): void
    {
        $user = User::factory()->create();
        $item = RoadmapItem::factory()->create(['status' => RoadmapStatus::Rejected]);

        $response = $this->actingAs($user)->post(route('roadmap.vote', $item), ['type' => 'up']);

        $response->assertNotFound();
        $this->assertDatabaseCount('roadmap_votes', 0);
    }

    public function test_vote_response_redirects_back(): void
    {
        $user = User::factory()->create();
        $item = RoadmapItem::factory()->approved()->create();

        $response = $this->actingAs($user)->post(route('roadmap.vote', $item), ['type' => 'up']);

        $response->assertRedirect();
    }

    public function test_voting_with_no_type_returns_validation_error(): void
    {
        $user = User::factory()->create();
        $item = RoadmapItem::factory()->approved()->create();

        $response = $this->actingAs($user)->post(route('roadmap.vote', $item), []);

        $response->assertSessionHasErrors('type');
        $this->assertDatabaseCount('roadmap_votes', 0);
    }

    public function test_voting_with_invalid_type_returns_validation_error(): void
    {
        $user = User::factory()->create();
        $item = RoadmapItem::factory()->approved()->create();

        $response = $this->actingAs($user)->post(route('roadmap.vote', $item), ['type' => 'sideways']);

        $response->assertSessionHasErrors('type');
        $this->assertDatabaseCount('roadmap_votes', 0);
    }

    public function test_cannot_vote_on_cancelled_item(): void
    {
        $user = User::factory()->create();
        $item = RoadmapItem::factory()->create(['status' => RoadmapStatus::Cancelled]);

        $response = $this->actingAs($user)->post(route('roadmap.vote', $item), ['type' => 'up']);

        $response->assertNotFound();
        $this->assertDatabaseCount('roadmap_votes', 0);
    }
}
