<?php

namespace Modules\Roadmap\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Roadmap\Events\StatusChanged;
use Modules\Roadmap\Notifications\RoadmapItemStatusChangedNotification;

class NotifySubmitter implements ShouldQueue
{
    public function handle(StatusChanged $event): void
    {
        $item = $event->item;
        $user = $item->user;

        if (! $user) {
            return;
        }

        $user->notify(new RoadmapItemStatusChangedNotification($item));
    }
}
