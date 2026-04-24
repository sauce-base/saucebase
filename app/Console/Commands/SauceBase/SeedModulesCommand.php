<?php

namespace App\Console\Commands\SauceBase;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use InterNACHI\Modular\Support\ModuleRegistry;

class SeedModulesCommand extends Command
{
    protected $signature = 'modules:seed';

    protected $description = 'Seed all installed modules that have a DatabaseSeeder';

    public function handle(ModuleRegistry $registry): int
    {
        foreach ($registry->modules() as $module) {
            $seeder = 'Modules\\'.Str::studly($module->name).'\\Database\\Seeders\\DatabaseSeeder';

            if (! class_exists($seeder)) {
                continue;
            }

            $this->components->task($module->name, fn () => $this->call('db:seed', ['--module' => strtolower($module->name)]) === self::SUCCESS);
        }

        return self::SUCCESS;
    }
}
