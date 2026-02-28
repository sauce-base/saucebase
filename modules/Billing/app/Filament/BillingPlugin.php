<?php

namespace Modules\Billing\Filament;

use App\Filament\ModulePlugin;
use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;

class BillingPlugin implements Plugin
{
    use ModulePlugin;

    public function getModuleName(): string
    {
        return 'Billing';
    }

    public function getId(): string
    {
        return 'billing';
    }

    public function boot(Panel $panel): void
    {
        $panel->navigationGroups([
            NavigationGroup::make()
                ->label(__('Billing'))
                ->icon(Heroicon::OutlinedCreditCard)
                ->collapsible(),
        ]);
    }
}
