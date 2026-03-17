<?php

namespace Modules\Roadmap\Filament\Resources\Roadmap\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Roadmap\Filament\Resources\Roadmap\RoadmapItemResource;

class EditRoadmapItem extends EditRecord
{
    protected static string $resource = RoadmapItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
