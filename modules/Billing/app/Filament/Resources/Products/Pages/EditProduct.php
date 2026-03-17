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

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->requiresConfirmation()
                ->successNotificationTitle(__('Product deleted successfully')),
            ForceDeleteAction::make()
                ->requiresConfirmation(),
            RestoreAction::make()
                ->successNotificationTitle(__('Product restored successfully')),
        ];
    }
}
