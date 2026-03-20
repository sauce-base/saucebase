<?php

namespace Modules\Auth\Filament\Resources\Users\Schemas;

use App\Enums\Role;
use Filament\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Modules\Auth\Exceptions\SocialiteException;
use Modules\Auth\Services\SocialiteService;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components(
                [
                    Section::make()
                        ->inlineLabel()
                        ->schema([
                            ImageEntry::make('avatar')
                                ->hiddenLabel()
                                ->circular(),
                            TextEntry::make('name')
                                ->label(__('Name'))
                                ->weight(FontWeight::Medium),
                            TextEntry::make('email')
                                ->label(__('Email address'))
                                ->state(fn ($record) => is_demo_mode() ? anonymize_email($record->email) : $record->email)
                                ->icon('heroicon-o-envelope'),
                            TextEntry::make('created_at')
                                ->label(__('Created at'))
                                ->dateTime()
                                ->icon('heroicon-o-calendar'),
                            TextEntry::make('updated_at')
                                ->label(__('Updated at'))
                                ->dateTime()
                                ->icon('heroicon-o-calendar'),
                            TextEntry::make('last_login_at')
                                ->label(__('Last login at'))
                                ->dateTime()
                                ->icon('heroicon-o-clock'),
                            TextEntry::make('last_activity_at')
                                ->label(__('Last activity at'))
                                ->dateTime()
                                ->icon('heroicon-o-clock'),
                            TextEntry::make('roles.name')
                                ->label(__('Role'))
                                ->badge()
                                ->color(fn (string $state): string => Role::fromString($state)->getColor())
                                ->default(__('No role assigned')),
                        ])
                        ->columnSpan(1),
                    Section::make(__('Social Accounts'))
                        ->schema([
                            RepeatableEntry::make('socialAccounts')
                                ->hiddenLabel()
                                ->schema([
                                    TextEntry::make('provider')
                                        ->label(__('Provider'))
                                        ->badge(),
                                    TextEntry::make('last_login_at')
                                        ->dateTime()
                                        ->label(__('Last Login'))
                                        ->placeholder(__('Never'))
                                        ->size('sm'),
                                    TextEntry::make('created_at')
                                        ->label(__('Connected'))
                                        ->since()
                                        ->size('sm')
                                        ->suffixAction(
                                            Action::make('disconnect')
                                                ->icon('heroicon-o-x-mark')
                                                ->iconButton()
                                                ->color('danger')
                                                ->tooltip(__('Disconnect'))
                                                ->requiresConfirmation()
                                                ->modalHeading(fn ($record) => __('Disconnect :provider?', ['provider' => ucfirst($record->provider)]))
                                                ->modalDescription(__('Are you sure you want to disconnect this social account? The user may lose access if this is their only login method without a password.'))
                                                ->action(function ($record, $livewire) {
                                                    $user = $livewire->getRecord();
                                                    $socialiteService = app(SocialiteService::class);

                                                    try {
                                                        $socialiteService->disconnectProvider($user, $record->provider);

                                                        Notification::make()
                                                            ->success()
                                                            ->title(__('Social account disconnected'))
                                                            ->body(__('The :provider account has been disconnected successfully.', ['provider' => ucfirst($record->provider)]))
                                                            ->send();
                                                    } catch (SocialiteException $e) {
                                                        Notification::make()
                                                            ->danger()
                                                            ->title(__('Cannot disconnect'))
                                                            ->body($e->getMessage())
                                                            ->send();

                                                        $livewire->halt();
                                                    }
                                                })
                                        ),
                                ])
                                ->columns(3)
                                ->hidden(fn ($record) => $record->socialAccounts()->count() === 0),
                            TextEntry::make('no_social_accounts')
                                ->label('')
                                ->state(__('This user has not connected any social accounts yet.'))
                                ->icon('heroicon-o-information-circle')
                                ->color('gray')
                                ->hidden(fn ($record) => $record->socialAccounts()->count() > 0),
                        ])
                        ->columnSpan(1),
                ]
            );
    }
}
