<?php

namespace Modules\Roadmap\Filament\Resources\Roadmap\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Modules\Roadmap\Enums\RoadmapStatus;
use Modules\Roadmap\Enums\RoadmapType;

class RoadmapItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('slug')
                    ->nullable()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder(__('Auto-generated from title')),
                Select::make('status')
                    ->options(RoadmapStatus::class)
                    ->default(RoadmapStatus::PendingApproval)
                    ->required(),
                Select::make('type')
                    ->options(RoadmapType::class)
                    ->default(RoadmapType::Feature)
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->nullable()
                    ->label(__('Submitted by')),
                Textarea::make('description')
                    ->nullable()
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
