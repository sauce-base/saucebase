<?php

namespace Modules\Auth\Filament\Resources\Users\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Auth\Filament\Resources\Users\UserResource;
use STS\FilamentImpersonate\Actions\Impersonate;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getFormActions(): array
    {
        return array_map(
            fn ($action) => $action->disabled(config('app.demo_mode')),
            parent::getFormActions(),
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()->disabled(config('app.demo_mode')),
            Impersonate::make()->record($this->getRecord()),
        ];
    }
}
