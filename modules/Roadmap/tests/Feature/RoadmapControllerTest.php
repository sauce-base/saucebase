<?php

namespace Modules\Roadmap\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Roadmap\Enums\RoadmapStatus;
use Modules\Roadmap\Enums\VoteType;
use Modules\Roadmap\Models\RoadmapItem;
use Modules\Roadmap\Models\RoadmapVote;
use Tests\TestCase;

class RoadmapControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('roadmap.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_index_only_returns_public_items(): void
    {
        $user = User::factory()->create();
        $approved = RoadmapItem::factory()->approved()->create(['title' => 'Approved feature']);
        RoadmapItem::factory()->create(['status' => RoadmapStatus::PendingApproval, 'title' => 'Pending feature']);
        RoadmapItem::factory()->create(['status' => RoadmapStatus::Rejected, 'title' => 'Rejected feature']);

        $response = $this->actingAs($user)->get(route('roadmap.index'));

        $response->assertInertia(fn ($page) => $page
            ->has('items', 1)
            ->where('items.0.id', $approved->id)
        );
    }

    public function test_items_include_user_vote_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $upvoted = RoadmapItem::factory()->approved()->create();
        $downvoted = RoadmapItem::factory()->approved()->create();

        RoadmapVote::create(['roadmap_item_id' => $upvoted->id, 'user_id' => $user->id, 'type' => VoteType::Up]);
        RoadmapVote::create(['roadmap_item_id' => $downvoted->id, 'user_id' => $user->id, 'type' => VoteType::Down]);

        $response = $this->actingAs($user)->get(route('roadmap.index'));

        $response->assertInertia(fn ($page) => $page
            ->has('items', 2)
            ->where('items.0.user_vote', 'up')
            ->where('items.1.user_vote', 'down')
        );
    }

    public function test_items_include_expected_fields(): void
    {
        $user = User::factory()->create();
        RoadmapItem::factory()->approved()->create();

        $response = $this->actingAs($user)->get(route('roadmap.index'));

        $response->assertInertia(fn ($page) => $page
            ->has('items.0', fn ($item) => $item
                ->has('id')
                ->has('title')
                ->has('description')
                ->has('status')
                ->has('status_label')
                ->has('type')
                ->has('type_label')
                ->has('net_score')
                ->has('user_vote')
                ->has('created_at')
            )
        );
    }

    public function test_sort_defaults_to_top(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('roadmap.index'));

        $response->assertInertia(fn ($page) => $page->where('sort', 'top'));
    }

    public function test_invalid_sort_falls_back_to_top(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('roadmap.index', ['sort' => 'invalid']));

        $response->assertInertia(fn ($page) => $page->where('sort', 'top'));
    }

    public function test_items_are_sorted_by_net_score_by_default(): void
    {
        $user = User::factory()->create();
        $popular = RoadmapItem::factory()->approved()->create();
        $less = RoadmapItem::factory()->approved()->create();

        RoadmapVote::create(['roadmap_item_id' => $popular->id, 'user_id' => User::factory()->create()->id, 'type' => VoteType::Up]);
        RoadmapVote::create(['roadmap_item_id' => $popular->id, 'user_id' => User::factory()->create()->id, 'type' => VoteType::Up]);
        RoadmapVote::create(['roadmap_item_id' => $less->id, 'user_id' => User::factory()->create()->id, 'type' => VoteType::Up]);

        $response = $this->actingAs($user)->get(route('roadmap.index'));

        $response->assertInertia(fn ($page) => $page
            ->where('items.0.id', $popular->id)
            ->where('items.1.id', $less->id)
        );
    }

    public function test_items_can_be_sorted_by_newest(): void
    {
        $user = User::factory()->create();
        $old = RoadmapItem::factory()->approved()->create(['created_at' => now()->subDays(5)]);
        $new = RoadmapItem::factory()->approved()->create(['created_at' => now()]);

        $response = $this->actingAs($user)->get(route('roadmap.index', ['sort' => 'new']));

        $response->assertInertia(fn ($page) => $page
            ->where('items.0.id', $new->id)
            ->where('items.1.id', $old->id)
        );
    }

    public function test_items_can_be_sorted_by_oldest(): void
    {
        $user = User::factory()->create();
        $old = RoadmapItem::factory()->approved()->create(['created_at' => now()->subDays(5)]);
        $new = RoadmapItem::factory()->approved()->create(['created_at' => now()]);

        $response = $this->actingAs($user)->get(route('roadmap.index', ['sort' => 'old']));

        $response->assertInertia(fn ($page) => $page
            ->where('items.0.id', $old->id)
            ->where('items.1.id', $new->id)
        );
    }

    public function test_only_50_items_are_returned(): void
    {
        $user = User::factory()->create();
        RoadmapItem::factory()->approved()->count(60)->create();

        $response = $this->actingAs($user)->get(route('roadmap.index'));

        $response->assertInertia(fn ($page) => $page->has('items', 50));
    }
}
