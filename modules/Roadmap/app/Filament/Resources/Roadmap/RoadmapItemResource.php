<?php

namespace Modules\Roadmap\Filament\Resources\Roadmap;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Roadmap\Filament\Resources\Roadmap\Pages\CreateRoadmapItem;
use Modules\Roadmap\Filament\Resources\Roadmap\Pages\EditRoadmapItem;
use Modules\Roadmap\Filament\Resources\Roadmap\Pages\ListRoadmapItems;
use Modules\Roadmap\Filament\Resources\Roadmap\Schemas\RoadmapItemForm;
use Modules\Roadmap\Filament\Resources\Roadmap\Tables\RoadmapItemsTable;
use Modules\Roadmap\Models\RoadmapItem;

class RoadmapItemResource extends Resource
{
    protected static ?string $model = RoadmapItem::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return RoadmapItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoadmapItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoadmapItems::route('/'),
            'create' => CreateRoadmapItem::route('/create'),
            'edit' => EditRoadmapItem::route('/{record}/edit'),
        ];
    }
}
