<?php

namespace Modules\Announcements\Filament\Resources\Announcements;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Announcements\Filament\Resources\Announcements\Pages\CreateAnnouncement;
use Modules\Announcements\Filament\Resources\Announcements\Pages\EditAnnouncement;
use Modules\Announcements\Filament\Resources\Announcements\Pages\ListAnnouncements;
use Modules\Announcements\Filament\Resources\Announcements\Schemas\AnnouncementForm;
use Modules\Announcements\Filament\Resources\Announcements\Tables\AnnouncementsTable;
use Modules\Announcements\Models\Announcement;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('announcements::filament.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return AnnouncementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnnouncementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAnnouncements::route('/'),
            'create' => CreateAnnouncement::route('/create'),
            'edit' => EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
