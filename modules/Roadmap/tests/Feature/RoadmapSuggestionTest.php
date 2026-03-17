<?php

namespace Modules\Roadmap\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Roadmap\Enums\RoadmapStatus;
use Modules\Roadmap\Enums\RoadmapType;
use Modules\Roadmap\Models\RoadmapItem;
use Tests\TestCase;

class RoadmapSuggestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_submit_suggestion(): void
    {
        $response = $this->post(route('roadmap.store'), [
            'title' => 'My feature',
            'type' => 'feature',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('roadmap_items', 0);
    }

    public function test_authenticated_user_can_submit_suggestion(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('roadmap.store'), [
            'title' => 'Dark mode support',
            'description' => 'Please add dark mode.',
            'type' => RoadmapType::Feature->value,
        ]);

        $this->assertDatabaseHas('roadmap_items', [
            'title' => 'Dark mode support',
            'description' => 'Please add dark mode.',
            'type' => RoadmapType::Feature->value,
            'user_id' => $user->id,
        ]);
    }

    public function test_new_suggestion_defaults_to_pending_approval(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('roadmap.store'), [
            'title' => 'New feature idea',
            'type' => RoadmapType::Feature->value,
        ]);

        $item = RoadmapItem::first();
        $this->assertEquals(RoadmapStatus::PendingApproval, $item->status);
    }

    public function test_new_suggestion_is_not_visible_on_public_roadmap(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('roadmap.store'), [
            'title' => 'Invisible suggestion',
            'type' => RoadmapType::Feature->value,
        ]);

        $public = RoadmapItem::public()->get();
        $this->assertCount(0, $public);
    }

    public function test_submission_redirects_to_roadmap_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('roadmap.store'), [
            'title' => 'Some feature',
            'type' => RoadmapType::Feature->value,
        ]);

        $response->assertRedirect(route('roadmap.index'));
    }

    public function test_submission_flashes_success_toast(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('roadmap.store'), [
            'title' => 'Toast feature',
            'type' => RoadmapType::Feature->value,
        ]);

        $response->assertSessionHas('toast.type', 'success');
        $response->assertSessionHas('toast.message');
        $response->assertSessionHas('toast.description');
    }

    public function test_title_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('roadmap.store'), [
            'title' => '',
            'type' => RoadmapType::Feature->value,
        ]);

        $response->assertSessionHasErrors('title');
        $this->assertDatabaseCount('roadmap_items', 0);
    }

    public function test_type_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('roadmap.store'), [
            'title' => 'Some feature',
            'type' => '',
        ]);

        $response->assertSessionHasErrors('type');
        $this->assertDatabaseCount('roadmap_items', 0);
    }

    public function test_type_must_be_valid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('roadmap.store'), [
            'title' => 'Some feature',
            'type' => 'invalid_type',
        ]);

        $response->assertSessionHasErrors('type');
        $this->assertDatabaseCount('roadmap_items', 0);
    }

    public function test_description_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('roadmap.store'), [
            'title' => 'Feature without description',
            'type' => RoadmapType::Feature->value,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('roadmap_items', [
            'title' => 'Feature without description',
            'description' => null,
        ]);
    }

    public function test_suggestion_is_associated_with_submitting_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('roadmap.store'), [
            'title' => 'User suggestion',
            'type' => RoadmapType::Bug->value,
        ]);

        $this->assertDatabaseHas('roadmap_items', [
            'title' => 'User suggestion',
            'user_id' => $user->id,
        ]);
    }

    public function test_title_cannot_exceed_255_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('roadmap.store'), [
            'title' => str_repeat('a', 256),
            'type' => RoadmapType::Feature->value,
        ]);

        $response->assertSessionHasErrors('title');
        $this->assertDatabaseCount('roadmap_items', 0);
    }

    public function test_description_cannot_exceed_2000_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('roadmap.store'), [
            'title' => 'Valid title',
            'description' => str_repeat('a', 2001),
            'type' => RoadmapType::Feature->value,
        ]);

        $response->assertSessionHasErrors('description');
        $this->assertDatabaseCount('roadmap_items', 0);
    }

    public function test_suggestion_submission_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->post(route('roadmap.store'), [
                'title' => "Feature {$i}",
                'type' => RoadmapType::Feature->value,
            ])->assertRedirect();
        }

        $response = $this->actingAs($user)->post(route('roadmap.store'), [
            'title' => 'One too many',
            'type' => RoadmapType::Feature->value,
        ]);

        $response->assertStatus(429);
    }
}
