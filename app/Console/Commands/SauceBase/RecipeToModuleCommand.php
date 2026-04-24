<?php

namespace App\Console\Commands\SauceBase;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Finder\Finder;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class RecipeToModuleCommand extends Command
{
    protected $signature = 'saucebase:recipe {module?} {template?}';

    protected $description = 'Create starter module from a recipe';

    protected string $moduleName = '';

    protected string $template = '';

    protected string $templatePath = '';

    protected string $tempFolder = '';

    protected string $moduleConfigPath = '';

    protected array $caseTypes = [
        'module' => 'strtolower',
        'Module' => 'ucwords',
        'model' => 'strtolower',
        'Model' => 'ucwords',
    ];

    public function handle(): bool
    {
        $this->moduleName = $this->getModuleName();

        $moduleConfigPath = base_path(config('app-modules.modules_directory', 'modules')).'/';
        $this->moduleConfigPath = Str::endsWith($moduleConfigPath, '/') ? $moduleConfigPath : $moduleConfigPath.'/';

        if (file_exists($this->moduleConfigPath.$this->moduleName)) {
            error("$this->moduleName module already exists.");

            return true;
        }

        $this->template = $this->getTemplate();
        $this->templatePath = base_path($this->template);
        $this->tempFolder = base_path('saucebase-temp');

        if (! file_exists($this->templatePath)) {
            error("$this->templatePath Path does not exist! Please check your config/saucebase.php file.");

            return true;
        }

        $this->generate();
        $this->registerInTaskfile();

        info('Starter '.$this->moduleName.' module generated successfully.');
        info('Next steps:');
        info('  composer update');
        info('  php artisan migrate');
        info('  php artisan modules:sync');
        info('  npm run build');

        return true;
    }

    protected function getModuleName(): string
    {
        $this->moduleName = ucwords($this->argument('module'));

        // Validation don't work when not in the prompt
        if ($this->moduleName !== '') {
            return $this->moduleName;
        }

        return ucwords(
            text(
                label: 'Please enter a name for the module to be created',
                required: true,
                validate: fn (string $value) => match (true) {
                    strlen($value) < 1 => 'The name must be at least 1 characters.',
                    Str::contains($value, ' ') => 'The name must not contain spaces.',
                    Str::contains($value, '-') => 'The name must not contain hyphens.',
                    file_exists($this->moduleConfigPath.$value) => 'Module already exists.',
                    default => null
                }
            )
        );
    }

    protected function getTemplate(): string
    {
        $template = $this->argument('template') ?? '';
        $templateConfig = config('saucebase.template');

        if ($template !== '') {
            if (in_array($template, array_keys($templateConfig))) {
                $template = $templateConfig[$template];
            } else {
                error("Invalid template option: $template");
                $template = '';
            }
        }

        if ($template === '') {
            $template = select(
                'Which recipe would you like to use?',
                array_keys($templateConfig)
            );
            $template = $templateConfig[$template];
        }

        return $template;
    }

    protected function generate(): void
    {
        // ensure directory does not exist
        $this->delete($this->tempFolder);
        $this->copy($this->templatePath, $this->tempFolder.'/Module');

        $finder = new Finder;
        $finder->files()->in($this->tempFolder);

        $this->renameFiles($finder);
        $this->updateFilesContent($finder);

        $this->copy($this->tempFolder.'/'.$this->moduleName, $this->moduleConfigPath.$this->moduleName);
        $this->delete($this->tempFolder);
    }

    protected function delete($sourceFile): void
    {
        $filesystem = new SymfonyFilesystem;
        if ($filesystem->exists($sourceFile)) {
            $filesystem->remove($sourceFile);
        }
    }

    protected function copy($sourceFile, $target): void
    {
        $filesystem = new SymfonyFilesystem;
        if ($filesystem->exists($sourceFile)) {
            $filesystem->mirror($sourceFile, $target);
        }
    }

    protected function renameFiles($finder): void
    {
        foreach ($finder as $file) {
            $type = Str::endsWith($file->getPath(), ['migrations', 'Migrations']) ? 'migration' : '';
            $sourceFile = $file->getPath().'/'.$file->getFilename();
            $this->alterFilename($sourceFile, $type);
        }
    }

    protected function alterFilename($sourceFile, $type = ''): void
    {
        $name = ucwords($this->moduleName);
        $model = Str::singular($name);

        $projectPath = base_path();
        $relativePath = substr($sourceFile, strlen($projectPath));

        $targetFile = $projectPath.str_replace(
            [
                'Module',
                'module',
                strtolower($name).'_plural',
                'Model',
                'model',
            ],
            [
                $name,
                Str::kebab($name),
                Str::kebab(Str::plural($name)),
                $model,
                Str::kebab($model),
            ],
            $relativePath
        );

        if (in_array(basename($sourceFile), config('saucebase.ignore_files'), true)) {
            $targetFile = dirname($targetFile).'/'.basename($sourceFile);
        }

        $targetFile = str_replace('Entities', 'Models', $targetFile);

        // hack to ensure modules if used does not get replaced
        if (Str::contains($targetFile, $name.'s')) {
            $targetFile = str_replace($name.'s', 'Modules', $targetFile);
        }

        $targetFile = str_replace($name.'_plural', Str::plural($name), $targetFile);

        if (
            ! is_dir(dirname($targetFile))
            && ! mkdir($concurrentDirectory = dirname($targetFile), 0777, true) && ! is_dir($concurrentDirectory)
        ) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        $this->rename($sourceFile, $targetFile, $type);
    }

    protected function rename($sourceFile, $targetFile, $type = ''): void
    {
        $filesystem = new SymfonyFilesystem;
        if ($filesystem->exists($sourceFile)) {
            if ($type === 'migration') {
                $targetFile = $this->appendTimestamp($targetFile);
            }
            $filesystem->rename($sourceFile, $targetFile, true);
        }
    }

    protected function appendTimestamp($sourceFile): array|string
    {
        $timestamp = date('Y_m_d_his_');
        $file = basename($sourceFile);

        return str_replace($file, $timestamp.$file, $sourceFile);
    }

    protected function updateFilesContent($finder): void
    {
        foreach ($finder as $file) {
            $sourceFile = $file->getPath().'/'.$file->getFilename();
            $this->replaceInFile($sourceFile);
        }
    }

    protected function replaceInFile($sourceFile): void
    {
        $name = ucwords($this->moduleName);
        $model = Str::singular($name);
        $types = $this->generatePlaceholderMap($name, $model);

        if (! file_exists($sourceFile)) {
            return;
        }

        $content = file_get_contents($sourceFile);
        $updated = str_replace(array_keys($types), array_values($types), $content);
        file_put_contents($sourceFile, $updated);
    }

    protected function generatePlaceholderMap(string $name, string $model): array
    {
        $spaceify = fn (string $string) => trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', $string));
        $titleCase = fn (string $string) => ucwords(str_replace('_', ' ', Str::snake($string)));

        $pluralName = Str::plural($name);
        $snakeName = Str::snake($name);
        $kebabName = Str::kebab($name);

        $snakePlural = Str::snake($pluralName);
        $kebabPlural = Str::kebab($pluralName);

        $modelSnake = Str::snake($model);
        $modelKebab = Str::kebab($model);
        $modelPlural = Str::plural($model);
        $modelSnakePlural = Str::snake($modelPlural);
        $modelKebabPlural = Str::kebab($modelPlural);

        return [
            // Module base
            '{Module}' => $name,
            '{module}' => strtolower($name),

            // Snake and kebab
            '{Module_}' => $this->renamePlaceholders($name, '_'),
            '{module_}' => $this->renamePlaceholders($name, '_', arrayMap: true),
            '{Module-}' => $this->renamePlaceholders($name, '-'),
            '{module-}' => $this->renamePlaceholders($name, '-', arrayMap: true),

            // Plural
            '{module_plural}' => $spaceify(strtolower($pluralName)),
            '{modulePlural}' => $spaceify(strtolower($pluralName)),
            '{module_plural_snake}' => $snakePlural,
            '{module_plural_kebab}' => $kebabPlural,

            // camelCase and StudlyCase
            '{moduleCamel}' => lcfirst($name),
            '{ModuleCamel}' => $name,
            '{moduleStudly}' => Str::studly($name),
            '{ModuleStudly}' => Str::studly($name),

            // Human readable
            '{Module }' => $spaceify($name),
            '{module }' => $spaceify(strtolower($name)),
            '{module_title}' => $titleCase($name),
            '{ModuleTitle}' => $titleCase($name),

            // Model base
            '{Model}' => $model,
            '{model}' => strtolower($model),

            // Snake and kebab
            '{Model_}' => $this->renamePlaceholders($model, '_'),
            '{model_}' => $this->renamePlaceholders($model, '_', arrayMap: true),
            '{Model-}' => $this->renamePlaceholders($model, '-'),
            '{model-}' => $this->renamePlaceholders($model, '-', arrayMap: true),

            // Plural
            '{model_plural}' => $spaceify(strtolower($modelPlural)),
            '{model_plural_snake}' => $modelSnakePlural,
            '{model_plural_kebab}' => $modelKebabPlural,

            // camelCase and StudlyCase
            '{modelCamel}' => lcfirst($model),
            '{ModelCamel}' => $model,
            '{modelStudly}' => Str::studly($model),
            '{ModelStudly}' => Str::studly($model),

            // Human readable
            '{Model }' => $spaceify($model),
            '{model }' => $spaceify(strtolower($model)),
            '{model_title}' => $titleCase($model),
            '{ModelTitle}' => $titleCase($model),

            // Undescore (JS friendly)
            '___Module___' => $name,
            '___module___' => strtolower($name),

            // MODULE_NAMESPACE
            '{MODULE_NAMESPACE}' => config('app-modules.modules_namespace', 'Modules'),
            '___MODULE_NAMESPACE___' => config('app-modules.modules_namespace', 'Modules'),
        ];
    }

    protected function renamePlaceholders($model, $separator, $arrayMap = null): string
    {
        $parts = preg_split('/(?=[A-Z])/', $model, -1, PREG_SPLIT_NO_EMPTY);

        if ($arrayMap) {
            $parts = array_map('strtolower', $parts);
        }

        return implode($separator, $parts);
    }

    protected function registerInTaskfile(): void
    {
        $taskfile = base_path('Taskfile.yml');

        if (! file_exists($taskfile)) {
            return;
        }

        $key = strtolower($this->moduleName);
        $content = file_get_contents($taskfile);

        if ($content === false) {
            throw new RuntimeException("Unable to read Taskfile at path [{$taskfile}].");
        }

        if (str_contains($content, "taskfile: ./modules/{$this->moduleName}/Taskfile.yml")) {
            return;
        }

        $entry = "    {$key}:\n        taskfile: ./modules/{$this->moduleName}/Taskfile.yml\n        optional: true\n";
        $markerPrefix = '    # ── END MODULES';

        $markerPosition = strpos($content, $markerPrefix);

        if ($markerPosition === false) {
            return;
        }

        $before = substr($content, 0, $markerPosition);
        $after = substr($content, $markerPosition);
        $updated = $before.$entry.$after;

        if ($updated === $content) {
            return;
        }

        file_put_contents($taskfile, $updated);
    }

    protected function append($sourceFile, $content): void
    {
        $filesystem = new SymfonyFilesystem;
        if ($filesystem->exists($sourceFile)) {
            $filesystem->appendToFile($sourceFile, $content);
        }
    }
}
