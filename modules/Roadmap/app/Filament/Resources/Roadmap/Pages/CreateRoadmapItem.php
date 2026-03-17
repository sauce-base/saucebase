<?php

namespace Modules\Roadmap\Filament\Resources\Roadmap\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Roadmap\Filament\Resources\Roadmap\RoadmapItemResource;

class CreateRoadmapItem extends CreateRecord
{
    protected static string $resource = RoadmapItemResource::class;
}
