<?php

namespace Modules\Announcements\Tests\Support;

use Modules\Announcements\Models\Announcement;

class AnnouncementTestHelper
{
    public static function clean(): void
    {
        Announcement::query()->delete();
    }
}
