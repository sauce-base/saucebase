<?php

namespace Modules\Announcements\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Announcements\Models\Announcement;

class AnnouncementsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Announcement::firstOrCreate(
            ['text' => '🎉 <strong>Announcements module is live!</strong> Manage banners like this one from the <a href="/admin/announcements">admin panel</a>.'],
            [
                'is_active' => true,
                'is_dismissable' => true,
                'show_on_frontend' => true,
                'show_on_dashboard' => true,
            ]
        );
    }
}
