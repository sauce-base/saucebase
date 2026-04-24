<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use InterNACHI\Modular\Support\ModuleRegistry;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\multisearch;

class GenerateModuleTypesCommand extends Command
{
    protected $name = 'module:generate-types';

    protected $description = 'Generate TypeScript types for one or more enabled modules';

    public function __construct()
    {
        parent::__construct();

        $this->getDefinition()->addArgument(
            new InputArgument('module', InputArgument::IS_ARRAY, 'Module name(s) to generate types for')
        );

        $this->getDefinition()->addOption(
            new InputOption('all', 'a', InputOption::VALUE_NONE, 'Generate types for all enabled modules')
        );
    }

    public function handle(): int
    {
        $modules = $this->resolveModules();

        $exitCode = self::SUCCESS;

        foreach ($modules as $name) {
            if ($this->generateForModule($name) === self::FAILURE) {
                $exitCode = self::FAILURE;
            }
        }

        return $exitCode;
    }

    /** @return array<string> */
    private function resolveModules(): array
    {
        $enabledModules = app(ModuleRegistry::class)->modules()->map->name->values()->all();

        if ($this->option('all')) {
            return $enabledModules;
        }

        $given = (array) $this->argument('module');

        if (! empty($given)) {
            return $given;
        }

        $selected = multisearch(
            label: 'Select modules',
            options: function (string $search) use ($enabledModules): array {
                return collect(['All', ...$enabledModules])
                    ->when(strlen($search) > 0, fn (Collection $items) => $items->filter(
                        fn ($item) => str_contains(strtolower($item), strtolower($search))
                    ))
                    ->values()
                    ->toArray();
            },
            required: 'You must select at least one module',
        );

        return in_array('All', $selected) ? $enabledModules : $selected;
    }

    private function generateForModule(string $name): int
    {
        $appPath = base_path("modules/{$name}/src");

        if (! is_dir($appPath)) {
            $this->components->error("Module app path not found: {$appPath}");

            return self::FAILURE;
        }

        $typesDir = base_path("modules/{$name}/resources/js/types");

        if (! is_dir($typesDir)) {
            mkdir($typesDir, 0755, true);
        }

        $this->components->task("Generate types · {$name}", function () use ($appPath, $typesDir): bool {
            $originalDiscoverTypes = Config::get('typescript-transformer.auto_discover_types');
            $originalOutputFile = Config::get('typescript-transformer.output_file');

            Config::set('typescript-transformer.auto_discover_types', [$appPath]);
            Config::set('typescript-transformer.output_file', "{$typesDir}/generated.d.ts");

            $output = $this->getOutput()->isVerbose() ? $this->output : null;
            Artisan::call('typescript:transform', [], $output);

            Config::set('typescript-transformer.auto_discover_types', $originalDiscoverTypes);
            Config::set('typescript-transformer.output_file', $originalOutputFile);

            return true;
        });

        return self::SUCCESS;
    }
}
