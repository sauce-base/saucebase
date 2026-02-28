<?php

namespace Modules\Billing\Filament\Resources\Customers\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Billing\Filament\Resources\Customers\CustomerResource;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
