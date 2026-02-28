<?php

namespace Modules\Auth\Filament\Resources\Users;

use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Modules\Auth\Filament\Resources\Users\Pages\CreateUser;
use Modules\Auth\Filament\Resources\Users\Pages\EditUser;
use Modules\Auth\Filament\Resources\Users\Pages\ListUsers;
use Modules\Auth\Filament\Resources\Users\Pages\ViewUser;
use Modules\Auth\Filament\Resources\Users\Schemas\UserForm;
use Modules\Auth\Filament\Resources\Users\Schemas\UserInfolist;
use Modules\Auth\Filament\Resources\Users\Tables\UsersTable;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Authentication');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        /** @var \App\Models\User $record */
        return $record->name;
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
