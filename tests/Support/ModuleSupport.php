<?php

namespace Tests\Support;

use InterNACHI\Modular\Support\ModuleRegistry;

class ModuleSupport
{
    public static function has(string $name): bool
    {
        return app(ModuleRegistry::class)->module($name) !== null;
    }
}
