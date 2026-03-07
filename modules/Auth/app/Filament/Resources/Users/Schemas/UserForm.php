<?php

namespace Modules\Auth\Filament\Resources\Users\Schemas;

use App\Enums\Role as RoleEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                SpatieMediaLibraryFileUpload::make('avatar')
                    ->avatar()
                    ->directory('avatars')
                    ->collection('avatars')
                    ->disk('public'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label(__('Email address'))
                    ->email()
                    ->required()
                    ->formatStateUsing(fn ($state) => config('app.demo_mode') ? anonymize_email($state ?? '') : $state)
                    ->disabled(fn () => config('app.demo_mode')),
                Select::make('roles')
                    ->label(__('Role'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->minItems(1)
                    ->preload()
                    // Optional: default to "user" on create:
                    ->default(fn () => [Role::where('name', RoleEnum::USER)->value('id')]),
                TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->hiddenOn('edit'),
                TextInput::make('password_confirmation')
                    ->label(__('Password confirmation'))
                    ->password()
                    ->hiddenOn('edit')
                    ->same('password'),
                TextInput::make('email_verified_at')
                    ->label(__('Email verified at'))
                    ->disabled(),
                TextInput::make('last_login_at')
                    ->label(__('Last login at'))
                    ->disabled(),
            ]);
    }
}
