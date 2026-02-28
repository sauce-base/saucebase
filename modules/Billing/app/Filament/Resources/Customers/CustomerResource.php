<?php

namespace Modules\Billing\Filament\Resources\Customers;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Modules\Billing\Filament\Resources\Customers\Pages\ListCustomers;
use Modules\Billing\Filament\Resources\Customers\Pages\ViewCustomer;
use Modules\Billing\Filament\Resources\Customers\Schemas\CustomerInfolist;
use Modules\Billing\Filament\Resources\Customers\Tables\CustomersTable;
use Modules\Billing\Models\Customer;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?int $navigationSort = 3;

    public static function getGloballySearchableAttributes(): array
    {
        return ['email', 'name', 'user.name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        /** @var \Modules\Billing\Models\Customer $record */
        return $record->email ?? $record->name ?? "Customer #{$record->id}";
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Billing');
    }

    public static function getNavigationLabel(): string
    {
        return __('Customers');
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'view' => ViewCustomer::route('/{record}'),
        ];
    }
}
