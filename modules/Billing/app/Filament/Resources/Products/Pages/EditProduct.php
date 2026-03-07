<?php

namespace Modules\Billing\Filament\Resources\Products\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Billing\Filament\Resources\Products\ProductResource;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getFormActions(): array
    {
        return array_map(
            fn ($action) => $action->disabled(config('app.demo_mode')),
            parent::getFormActions(),
        );
    }

    protected function getHeaderActions(): array
    {
        $isDemo = config('app.demo_mode');

        return [
            ViewAction::make(),
            DeleteAction::make()
                ->requiresConfirmation()
                ->successNotificationTitle(__('Product deleted successfully'))
                ->disabled($isDemo),
            ForceDeleteAction::make()
                ->requiresConfirmation()
                ->disabled($isDemo),
            RestoreAction::make()
                ->successNotificationTitle(__('Product restored successfully'))
                ->disabled($isDemo),
        ];
    }
}
