<?php

namespace Modules\Roadmap\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Roadmap\Enums\RoadmapStatus;
use Modules\Roadmap\Enums\RoadmapType;
use Modules\Roadmap\Models\RoadmapItem;

class RoadmapDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title' => 'Two-factor authentication (TOTP)',
                'description' => 'Add support for time-based one-time passwords using authenticator apps like Google Authenticator or Authy.',
                'status' => RoadmapStatus::InProgress,
                'type' => RoadmapType::Feature,
            ],
            [
                'title' => 'Team / workspace support',
                'description' => 'Allow users to create and manage teams, invite members, and switch between workspaces.',
                'status' => RoadmapStatus::Approved,
                'type' => RoadmapType::Feature,
            ],
            [
                'title' => 'API rate limiting & developer keys',
                'description' => 'Provide API keys for developers with configurable rate limits and usage analytics.',
                'status' => RoadmapStatus::Approved,
                'type' => RoadmapType::Feature,
            ],
            [
                'title' => 'CSV / Excel data export',
                'description' => 'Allow users to export their account data to CSV or Excel format for use in external tools.',
                'status' => RoadmapStatus::PendingApproval,
                'type' => RoadmapType::Feature,
            ],
            [
                'title' => 'Audit log for account activity',
                'description' => 'Track and display a history of significant account actions such as logins, setting changes, and billing events.',
                'status' => RoadmapStatus::PendingApproval,
                'type' => RoadmapType::Feature,
            ],
            [
                'title' => 'Webhook notifications',
                'description' => 'Send real-time HTTP webhook events to user-configured endpoints when key actions occur in their account.',
                'status' => RoadmapStatus::PendingApproval,
                'type' => RoadmapType::Feature,
            ],
            [
                'title' => 'Impersonate user (admin tool)',
                'description' => 'Allow admins to log in as any user to debug issues and provide support without needing their password.',
                'status' => RoadmapStatus::Completed,
                'type' => RoadmapType::Feature,
            ],
            [
                'title' => 'Fix invoice PDF rendering on Safari',
                'description' => 'Invoice PDFs fail to render correctly in Safari due to a CSS compatibility issue. Affects macOS and iOS users.',
                'status' => RoadmapStatus::Completed,
                'type' => RoadmapType::Bug,
            ],
            [
                'title' => 'Login page flickers on redirect',
                'description' => 'After a successful login, there is a brief white flash before the dashboard loads. Needs smoother transition.',
                'status' => RoadmapStatus::Approved,
                'type' => RoadmapType::Bug,
            ],
            [
                'title' => 'Improve onboarding checklist UI',
                'description' => 'The onboarding checklist is hard to dismiss and lacks progress indicators. Redesign for a cleaner first-run experience.',
                'status' => RoadmapStatus::PendingApproval,
                'type' => RoadmapType::Improvement,
            ],
        ];

        foreach ($items as $item) {
            RoadmapItem::updateOrCreate(
                ['title' => $item['title']],
                $item,
            );
        }
    }
}
