<?php

namespace Modules\Announcements\Policies;

use App\Models\User;
use Modules\Announcements\Models\Announcement;

class AnnouncementPolicy
{
    public function create(User $user): bool
    {
        return ! is_demo_mode();
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return ! is_demo_mode();
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return ! is_demo_mode();
    }

    public function deleteAny(User $user): bool
    {
        return ! is_demo_mode();
    }
}
