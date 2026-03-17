<?php

namespace Modules\Roadmap\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Modules\Roadmap\Enums\RoadmapStatus;
use Modules\Roadmap\Events\StatusChanged;
use Modules\Roadmap\Models\RoadmapItem;
use Modules\Roadmap\Notifications\RoadmapItemStatusChangedNotification;
use Tests\TestCase;

class RoadmapNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_is_dispatched_when_status_changes(): void
    {
        Event::fake([StatusChanged::class]);

        $item = RoadmapItem::factory()->create(['status' => RoadmapStatus::PendingApproval]);

        $item->update(['status' => RoadmapStatus::Approved]);

        Event::assertDispatched(StatusChanged::class, function ($event) use ($item) {
            return $event->item->is($item);
        });
    }

    public function test_event_is_not_dispatched_when_status_does_not_change(): void
    {
        Event::fake([StatusChanged::class]);

        $item = RoadmapItem::factory()->create(['status' => RoadmapStatus::Approved]);

        $item->update(['title' => 'Updated title only']);

        Event::assertNotDispatched(StatusChanged::class);
    }

    public function test_user_is_notified_when_status_changes(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $item = RoadmapItem::factory()->create([
            'status' => RoadmapStatus::PendingApproval,
            'user_id' => $user->id,
        ]);

        $item->update(['status' => RoadmapStatus::Approved]);

        Notification::assertSentTo($user, RoadmapItemStatusChangedNotification::class);
    }

    public function test_notification_is_not_sent_when_status_does_not_change(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $item = RoadmapItem::factory()->create([
            'status' => RoadmapStatus::Approved,
            'user_id' => $user->id,
        ]);

        $item->update(['title' => 'Updated title only']);

        Notification::assertNothingSent();
    }

    public function test_notification_is_not_sent_for_items_without_a_user(): void
    {
        Notification::fake();

        $item = RoadmapItem::factory()->create([
            'status' => RoadmapStatus::PendingApproval,
            'user_id' => null,
        ]);

        $item->update(['status' => RoadmapStatus::Approved]);

        Notification::assertNothingSent();
    }

    public function test_notification_email_contains_item_title(): void
    {
        $user = User::factory()->create(['name' => 'Jane Doe']);
        $item = RoadmapItem::factory()->create([
            'title' => 'Dark mode support',
            'status' => RoadmapStatus::Approved,
            'user_id' => $user->id,
        ]);

        $notification = new RoadmapItemStatusChangedNotification($item);
        $mail = $notification->toMail($user);

        $this->assertStringContainsString('Dark mode support', implode(' ', $mail->introLines));
    }

    public function test_notification_email_contains_new_status(): void
    {
        $user = User::factory()->create();
        $item = RoadmapItem::factory()->inProgress()->create(['user_id' => $user->id]);

        $notification = new RoadmapItemStatusChangedNotification($item);
        $mail = $notification->toMail($user);

        $this->assertStringContainsString('In Progress', implode(' ', $mail->introLines));
    }

    public function test_notification_email_has_roadmap_action_link(): void
    {
        $user = User::factory()->create();
        $item = RoadmapItem::factory()->approved()->create(['user_id' => $user->id]);

        $notification = new RoadmapItemStatusChangedNotification($item);
        $mail = $notification->toMail($user);

        $this->assertEquals(route('roadmap.index'), $mail->actionUrl);
    }

    public function test_notification_is_sent_for_each_status_transition(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $item = RoadmapItem::factory()->create([
            'status' => RoadmapStatus::PendingApproval,
            'user_id' => $user->id,
        ]);

        $item->update(['status' => RoadmapStatus::Approved]);
        $item->update(['status' => RoadmapStatus::InProgress]);
        $item->update(['status' => RoadmapStatus::Completed]);

        Notification::assertSentToTimes($user, RoadmapItemStatusChangedNotification::class, 3);
    }
}
