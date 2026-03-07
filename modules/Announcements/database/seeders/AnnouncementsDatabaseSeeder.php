<?php

namespace Modules\Announcements\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Announcements\Models\Announcement;

class AnnouncementsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Announcement::updateOrCreate([
            'text' => '🧪 <strong>Demo Mode</strong> — This is a sandbox environment. No real charges will be made. The <a href="/admin/announcements" target="_blank">admin panel</a> is read-only.',
        ], [
            'is_active' => true,
            'is_dismissable' => true,
            'show_on_frontend' => true,
            'show_on_dashboard' => true,
        ]);
    }
}
