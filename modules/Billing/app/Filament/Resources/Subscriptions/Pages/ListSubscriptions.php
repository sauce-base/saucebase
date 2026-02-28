<?php

namespace Modules\Billing\Filament\Resources\Subscriptions\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Billing\Filament\Resources\Subscriptions\SubscriptionResource;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
