<?php

namespace Modules\Roadmap\Filament;

use App\Filament\ModulePlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class RoadmapPlugin implements Plugin
{
    use ModulePlugin;

    public function getModuleName(): string
    {
        return 'Roadmap';
    }

    public function getId(): string
    {
        return 'roadmap';
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
