<?php

namespace Modules\Announcements\Filament\Resources\Announcements\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Announcements\Filament\Resources\Announcements\AnnouncementResource;

class EditAnnouncement extends EditRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function getFormActions(): array
    {
        return array_map(
            fn ($action) => $action->disabled(config('app.demo_mode')),
            parent::getFormActions(),
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->disabled(config('app.demo_mode')),
        ];
    }
}
