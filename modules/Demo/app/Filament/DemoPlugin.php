<?php

namespace Modules\Demo\Filament;

use App\Filament\ModulePlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class DemoPlugin implements Plugin
{
    use ModulePlugin;

    public function getModuleName(): string
    {
        return 'Demo';
    }

    public function getId(): string
    {
        return 'demo';
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
