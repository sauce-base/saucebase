<?php

namespace Modules\Blog\Filament\Resources\Blog\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Modules\Blog\Enums\PostStatus;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->columnSpanFull(),

                TextInput::make('slug')
                    ->nullable()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder(__('Auto-generated from title'))
                    ->columnSpanFull(),

                SpatieMediaLibraryFileUpload::make('cover')
                    ->collection('cover')
                    ->disk('public')
                    ->visibility('public')
                    ->image()
                    ->columnSpanFull(),

                RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('excerpt')
                    ->nullable()
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),

                Select::make('status')
                    ->options(PostStatus::class)
                    ->default(PostStatus::Draft)
                    ->required(),

                DateTimePicker::make('published_at')
                    ->nullable()
                    ->label(__('Publish date')),

                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->nullable()
                    ->preload(),

                Select::make('author_id')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->nullable()
                    ->preload()
                    ->label(__('Author')),
            ]);
    }
}
