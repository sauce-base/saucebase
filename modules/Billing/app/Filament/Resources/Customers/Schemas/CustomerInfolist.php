<?php

namespace Modules\Billing\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Billing\Enums\SubscriptionStatus;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Grid::make(1)
                    ->schema([
                        Section::make(__('Customer Details'))
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label(__('User')),

                                TextEntry::make('name')
                                    ->label(__('Display Name'))
                                    ->hidden(fn ($record) => blank($record->name)),

                                TextEntry::make('email')
                                    ->label(__('Email'))
                                    ->copyable(),

                                TextEntry::make('phone')
                                    ->label(__('Phone'))
                                    ->hidden(fn ($record) => blank($record->phone)),

                                TextEntry::make('provider_customer_id')
                                    ->label(__('Provider ID'))
                                    ->copyable()
                                    ->hidden(fn ($record) => blank($record->provider_customer_id)),

                                TextEntry::make('created_at')
                                    ->label(__('Created At'))
                                    ->dateTime(),
                            ])
                            ->columns(2),

                        Section::make(__('Subscriptions'))
                            ->schema([
                                RepeatableEntry::make('subscriptions')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextEntry::make('price.product.name')
                                            ->label(__('Product')),

                                        TextEntry::make('status')
                                            ->label(__('Status'))
                                            ->badge()
                                            ->color(fn (SubscriptionStatus $state): string => match ($state) {
                                                SubscriptionStatus::Active => 'success',
                                                SubscriptionStatus::PastDue => 'warning',
                                                SubscriptionStatus::Cancelled => 'danger',
                                                SubscriptionStatus::Pending => 'gray',
                                            }),

                                        TextEntry::make('current_period_ends_at')
                                            ->label(__('Period End'))
                                            ->dateTime(),
                                    ])
                                    ->columns(3)
                                    ->hidden(fn ($record) => $record->subscriptions->isEmpty()),

                                TextEntry::make('no_subscriptions')
                                    ->hiddenLabel()
                                    ->state(__('No subscriptions found.'))
                                    ->icon('heroicon-o-information-circle')
                                    ->color('gray')
                                    ->hidden(fn ($record) => $record->subscriptions->isNotEmpty()),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(1),

                Grid::make(1)
                    ->schema([
                        Section::make(__('Payment Methods'))
                            ->schema([
                                RepeatableEntry::make('paymentMethods')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextEntry::make('type')
                                            ->label(__('Type'))
                                            ->badge(),

                                        TextEntry::make('created_at')
                                            ->label(__('Added'))
                                            ->dateTime(),
                                    ])
                                    ->columns(2)
                                    ->hidden(fn ($record) => $record->paymentMethods->isEmpty()),

                                TextEntry::make('no_payment_methods')
                                    ->hiddenLabel()
                                    ->state(__('No payment methods found.'))
                                    ->icon('heroicon-o-information-circle')
                                    ->color('gray')
                                    ->hidden(fn ($record) => $record->paymentMethods->isNotEmpty()),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(1),
            ]);
    }
}
