<?php

namespace Modules\Roadmap\Tests\Support;

use Modules\Roadmap\Models\RoadmapItem;
use Modules\Roadmap\Models\RoadmapVote;

class RoadmapTestHelper
{
    public static function clean(): void
    {
        RoadmapVote::query()->delete();
        RoadmapItem::query()->delete();
    }
}
