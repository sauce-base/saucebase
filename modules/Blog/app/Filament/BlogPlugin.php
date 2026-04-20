<?php

namespace Modules\Blog\Filament;

use App\Filament\ModulePlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class BlogPlugin implements Plugin
{
    use ModulePlugin;

    public function getModuleName(): string
    {
        return 'Blog';
    }

    public function getId(): string
    {
        return 'blog';
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
