<?php

namespace Modules\Billing\Filament\Resources\Products\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Billing\Filament\Resources\Products\ProductResource;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
