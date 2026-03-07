<?php

namespace Modules\Announcements\Filament\Resources\Announcements\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Announcements\Filament\Resources\Announcements\AnnouncementResource;

class CreateAnnouncement extends CreateRecord
{
    protected static string $resource = AnnouncementResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
