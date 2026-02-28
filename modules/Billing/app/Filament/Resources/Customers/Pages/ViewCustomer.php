<?php

namespace Modules\Billing\Filament\Resources\Customers\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\Billing\Filament\Resources\Customers\CustomerResource;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
