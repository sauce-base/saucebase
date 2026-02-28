<?php

namespace Modules\Billing\Filament\Resources\Products\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Billing\Filament\Resources\Products\ProductResource;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
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
