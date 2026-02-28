<?php

namespace Modules\Billing\Filament\Resources\Subscriptions;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Modules\Billing\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use Modules\Billing\Filament\Resources\Subscriptions\Pages\ViewSubscription;
use Modules\Billing\Filament\Resources\Subscriptions\Schemas\SubscriptionInfolist;
use Modules\Billing\Filament\Resources\Subscriptions\Tables\SubscriptionsTable;
use Modules\Billing\Models\Subscription;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?int $navigationSort = 2;

    public static function getGloballySearchableAttributes(): array
    {
        return ['provider_subscription_id', 'customer.email'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        /** @var \Modules\Billing\Models\Subscription $record */
        return $record->provider_subscription_id ?? "Subscription #{$record->id}";
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Billing');
    }

    public static function getNavigationLabel(): string
    {
        return __('Subscriptions');
    }

    public static function table(Table $table): Table
    {
        return SubscriptionsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SubscriptionInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptions::route('/'),
            'view' => ViewSubscription::route('/{record}'),
        ];
    }
}
