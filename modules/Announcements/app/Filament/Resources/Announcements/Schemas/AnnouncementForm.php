<?php

namespace Modules\Announcements\Filament\Resources\Announcements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                RichEditor::make('text')
                    ->required()
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike'],
                        ['link'],
                        ['bulletList', 'orderedList'],
                    ])
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->default(false),
                Toggle::make('is_dismissable')
                    ->default(false),
                Toggle::make('show_on_frontend')
                    ->label(__('announcements::filament.show_on_frontend'))
                    ->default(true),
                Toggle::make('show_on_dashboard')
                    ->label(__('announcements::filament.show_on_dashboard'))
                    ->default(true),
                DateTimePicker::make('starts_at')
                    ->nullable(),
                DateTimePicker::make('ends_at')
                    ->nullable()
                    ->rules(['nullable', 'after_or_equal:starts_at']),
            ]);
    }
}
