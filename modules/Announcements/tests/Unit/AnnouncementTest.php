<?php

namespace Modules\Announcements\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Announcements\Models\Announcement;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_null_when_no_announcements_exist(): void
    {
        $result = Announcement::active()->first();

        $this->assertNull($result);
    }

    public function test_returns_null_when_announcement_is_inactive(): void
    {
        Announcement::factory()->create(['is_active' => false]);

        $result = Announcement::active()->first();

        $this->assertNull($result);
    }

    public function test_returns_announcement_when_active_and_no_schedule_set(): void
    {
        $announcement = Announcement::factory()->active()->create();

        $result = Announcement::active()->first();

        $this->assertNotNull($result);
        $this->assertEquals($announcement->id, $result->id);
    }

    public function test_returns_null_when_starts_at_is_in_the_future(): void
    {
        Announcement::factory()->active()->create([
            'starts_at' => now()->addHour(),
        ]);

        $result = Announcement::active()->first();

        $this->assertNull($result);
    }

    public function test_returns_null_when_ends_at_is_in_the_past(): void
    {
        Announcement::factory()->active()->create([
            'ends_at' => now()->subHour(),
        ]);

        $result = Announcement::active()->first();

        $this->assertNull($result);
    }

    public function test_returns_announcement_when_within_schedule_window(): void
    {
        $announcement = Announcement::factory()->active()->create([
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
        ]);

        $result = Announcement::active()->first();

        $this->assertNotNull($result);
        $this->assertEquals($announcement->id, $result->id);
    }

    public function test_returns_latest_when_multiple_active_announcements_exist(): void
    {
        Announcement::factory()->active()->create([
            'created_at' => now()->subDay(),
        ]);
        $latest = Announcement::factory()->active()->create([
            'created_at' => now(),
        ]);

        $result = Announcement::active()->first();

        $this->assertNotNull($result);
        $this->assertEquals($latest->id, $result->id);
    }
}
