<?php

namespace Modules\Roadmap\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Roadmap\Models\RoadmapItem;

class StatusChanged
{
    use Dispatchable;

    public function __construct(
        public RoadmapItem $item,
    ) {}
}
