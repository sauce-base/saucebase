<?php

namespace Modules\Blog\Filament\Resources\Blog\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Blog\Filament\Resources\Blog\CategoryResource;
use Modules\Blog\Filament\Resources\Blog\PostResource;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('categories')
                ->label(__('Manage Categories'))
                ->icon('heroicon-o-tag')
                ->url(CategoryResource::getUrl())
                ->color('gray'),
            CreateAction::make(),
        ];
    }
}
