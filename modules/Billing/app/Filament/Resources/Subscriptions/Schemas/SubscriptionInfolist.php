<?php

namespace Modules\Billing\Filament\Resources\Subscriptions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Billing\Enums\SubscriptionStatus;

class SubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Grid::make(1)
                    ->schema([
                        Section::make(__('Subscription Details'))
                            ->schema([
                                TextEntry::make('provider_subscription_id')
                                    ->label(__('Provider ID'))
                                    ->copyable()
                                    ->columnSpanFull(),

                                TextEntry::make('status')
                                    ->label(__('Status'))
                                    ->badge()
                                    ->color(fn (SubscriptionStatus $state): string => match ($state) {
                                        SubscriptionStatus::Active => 'success',
                                        SubscriptionStatus::PastDue => 'warning',
                                        SubscriptionStatus::Cancelled => 'danger',
                                        SubscriptionStatus::Pending => 'gray',
                                    }),

                                TextEntry::make('price.product.name')
                                    ->label(__('Product')),

                                TextEntry::make('price.formatted_price')
                                    ->label(__('Plan Price'))
                                    ->state(fn ($record) => $record->price
                                        ? '$'.number_format($record->price->amount / 100, 2).' '.
                                            $record->price->currency->value.
                                            ($record->price->interval ? '/'.$record->price->interval : ' (one-time)')
                                        : null
                                    ),

                                TextEntry::make('current_period_starts_at')
                                    ->label(__('Period Start'))
                                    ->dateTime(),

                                TextEntry::make('current_period_ends_at')
                                    ->label(__('Period End'))
                                    ->dateTime(),

                                TextEntry::make('trial_starts_at')
                                    ->label(__('Trial Start'))
                                    ->dateTime()
                                    ->hidden(fn ($record) => $record->trial_starts_at === null),

                                TextEntry::make('trial_ends_at')
                                    ->label(__('Trial End'))
                                    ->dateTime()
                                    ->hidden(fn ($record) => $record->trial_ends_at === null),

                                TextEntry::make('cancelled_at')
                                    ->label(__('Cancelled At'))
                                    ->dateTime()
                                    ->hidden(fn ($record) => $record->cancelled_at === null),

                                TextEntry::make('ends_at')
                                    ->label(__('Ends At'))
                                    ->dateTime()
                                    ->hidden(fn ($record) => $record->ends_at === null),

                                TextEntry::make('created_at')
                                    ->label(__('Created At'))
                                    ->dateTime(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(1),

                Grid::make(1)
                    ->schema([
                        Section::make(__('Customer'))
                            ->schema([
                                TextEntry::make('customer.user.name')
                                    ->label(__('Name')),

                                TextEntry::make('customer.email')
                                    ->label(__('Email')),

                                TextEntry::make('customer.phone')
                                    ->label(__('Phone'))
                                    ->hidden(fn ($record) => blank($record->customer?->phone)),
                            ])
                            ->columns(1),

                        Section::make(__('Payment Method'))
                            ->schema([
                                TextEntry::make('paymentMethod.type')
                                    ->label(__('Type'))
                                    ->badge(),
                            ])
                            ->collapsible()
                            ->hidden(fn ($record) => $record->paymentMethod === null),
                    ])
                    ->columnSpan(1),
            ]);
    }
}
