<?php

namespace Modules\Billing\Filament\Resources\Subscriptions\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\Billing\Filament\Resources\Subscriptions\SubscriptionResource;

class ViewSubscription extends ViewRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
