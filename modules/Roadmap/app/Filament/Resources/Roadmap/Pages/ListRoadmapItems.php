<?php

namespace Modules\Roadmap\Filament\Resources\Roadmap\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Roadmap\Filament\Resources\Roadmap\RoadmapItemResource;

class ListRoadmapItems extends ListRecords
{
    protected static string $resource = RoadmapItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
