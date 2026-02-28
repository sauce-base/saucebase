<?php

namespace Modules\Auth\Filament;

use App\Filament\ModulePlugin;
use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;

class AuthPlugin implements Plugin
{
    use ModulePlugin;

    public function getModuleName(): string
    {
        return 'Auth';
    }

    public function getId(): string
    {
        return 'auth';
    }

    public function boot(Panel $panel): void
    {
        $panel->navigationGroups([
            NavigationGroup::make()
                ->label(__('Authentication'))
                ->icon(Heroicon::OutlinedShieldCheck)
                ->collapsible(),
        ]);
    }
}
