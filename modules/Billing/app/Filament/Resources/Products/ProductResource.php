<?php

namespace Modules\Billing\Filament\Resources\Products;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Modules\Billing\Filament\Resources\Products\Pages\CreateProduct;
use Modules\Billing\Filament\Resources\Products\Pages\EditProduct;
use Modules\Billing\Filament\Resources\Products\Pages\ListProducts;
use Modules\Billing\Filament\Resources\Products\Pages\ViewProduct;
use Modules\Billing\Filament\Resources\Products\Schemas\ProductForm;
use Modules\Billing\Filament\Resources\Products\Schemas\ProductInfolist;
use Modules\Billing\Filament\Resources\Products\Tables\ProductsTable;
use Modules\Billing\Models\Product;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku', 'slug'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        /** @var \Modules\Billing\Models\Product $record */
        return $record->name;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Billing');
    }

    public static function getNavigationLabel(): string
    {
        return __('Products');
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
