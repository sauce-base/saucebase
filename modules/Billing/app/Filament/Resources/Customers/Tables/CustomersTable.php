<?php

namespace Modules\Billing\Filament\Resources\Customers\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('User'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),

                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('subscriptions_count')
                    ->label(__('Subscriptions'))
                    ->counts('subscriptions')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
