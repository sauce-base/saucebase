<?php

namespace Modules\Billing\Filament\Resources\Subscriptions\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Billing\Enums\SubscriptionStatus;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.user.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.email')
                    ->label(__('Email'))
                    ->searchable(),

                TextColumn::make('price.product.name')
                    ->label(__('Product')),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (SubscriptionStatus $state): string => match ($state) {
                        SubscriptionStatus::Active => 'success',
                        SubscriptionStatus::PastDue => 'warning',
                        SubscriptionStatus::Cancelled => 'danger',
                        SubscriptionStatus::Pending => 'gray',
                    }),

                TextColumn::make('current_period_ends_at')
                    ->label(__('Period Ends'))
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(SubscriptionStatus::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
