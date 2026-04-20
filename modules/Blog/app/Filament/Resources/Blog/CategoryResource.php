<?php

namespace Modules\Blog\Filament\Resources\Blog;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Blog\Filament\Resources\Blog\Pages\CreateCategory;
use Modules\Blog\Filament\Resources\Blog\Pages\EditCategory;
use Modules\Blog\Filament\Resources\Blog\Pages\ListCategories;
use Modules\Blog\Filament\Resources\Blog\Schemas\CategoryForm;
use Modules\Blog\Filament\Resources\Blog\Tables\CategoriesTable;
use Modules\Blog\Models\Category;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
