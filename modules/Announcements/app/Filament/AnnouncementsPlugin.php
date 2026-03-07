<?php

namespace Modules\Announcements\Filament;

use App\Filament\ModulePlugin;
use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;

class AnnouncementsPlugin implements Plugin
{
    use ModulePlugin;

    public function getModuleName(): string
    {
        return 'Announcements';
    }

    public function getId(): string
    {
        return 'announcements';
    }

    public function boot(Panel $panel): void
    {
        $panel->navigationGroups([
            NavigationGroup::make()
                ->label(__('announcements::filament.navigation_group'))
                ->icon(Heroicon::OutlinedMegaphone)
                ->collapsible(),
        ]);
    }
}
