<?php

namespace App\Console\Commands\SauceBase;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Nwidart\Modules\Facades\Module;

class InstallCommand extends Command
{
    protected $signature = 'saucebase:install
                            {--fresh : Run migrate:fresh instead of migrate (destructive)}
                            {--all-modules : Enable and migrate all available modules without prompting}
                            {--modules= : Comma-separated list of modules to enable (e.g. Auth,Settings)}
                            {--force : Skip confirmations}';

    protected $description = 'Install and configure Saucebase';

    public function handle(): int
    {
        $this->displayWelcome();

        if ($this->isCI()) {
            return $this->handleCIInstallation();
        }

        return $this->install();
    }

    protected function install(): int
    {
        if (! $this->ensureEnvFile()) {
            return self::FAILURE;
        }

        $this->generateApplicationKey();
        $this->setupDatabase();
        $this->setupModules();
        $this->createStorageLink();
        $this->clearCaches();
        $this->displaySuccess();

        return self::SUCCESS;
    }

    protected function handleCIInstallation(): int
    {
        $this->info('CI environment detected - running minimal setup...');

        $this->components->task('Verifying .env', fn () => file_exists(base_path('.env')));
        $this->components->task('Verifying app key', fn () => ! empty(config('app.key')));

        $this->info('CI setup complete');

        return self::SUCCESS;
    }

    protected function ensureEnvFile(): bool
    {
        if (file_exists(base_path('.env'))) {
            return true;
        }

        if (file_exists(base_path('.env.example'))) {
            if (! copy(base_path('.env.example'), base_path('.env'))) {
                $this->error('Failed to copy .env.example to .env. Check directory permissions.');

                return false;
            }

            return true;
        }

        $this->error('.env file not found. Copy .env.example to .env and configure it before running the installer.');

        return false;
    }

    protected function generateApplicationKey(): void
    {
        $this->components->task('Generating application key', function () {
            $env = file_get_contents(base_path('.env'));
            if (preg_match('/^APP_KEY=base64:.+$/m', $env)) {
                return true;
            }

            return Artisan::call('key:generate', ['--force' => true]) === 0;
        });
    }

    protected function setupDatabase(): void
    {
        if ($this->option('fresh')) {
            $this->components->task('Running migrate:fresh --seed', function () {
                return Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]) === 0;
            });
        } else {
            $this->components->task('Running migrations', function () {
                return Artisan::call('migrate', ['--seed' => true, '--force' => true]) === 0;
            });
        }
    }

    protected function setupModules(): void
    {
        // After --fresh the DB is empty, so all modules need migration/seeding regardless
        // of their enabled status in modules_statuses.json. Without --fresh, only offer
        // disabled modules to avoid re-seeding non-idempotent seeders.
        $available = $this->option('fresh')
            ? array_keys(Module::all())
            : array_keys(Module::allDisabled());

        if (empty($available)) {
            return;
        }

        $selected = $this->resolveModuleSelection($available);

        if (empty($selected)) {
            return;
        }

        $this->newLine();

        foreach ($selected as $module) {
            $this->components->task("Enabling {$module} module", function () use ($module) {
                return Artisan::call('module:enable', ['module' => $module]) === 0;
            });

            $this->components->task("Migrating {$module} module", function () use ($module) {
                return Artisan::call('module:migrate', ['module' => $module, '--seed' => true, '--force' => true]) === 0;
            });
        }
    }

    /**
     * @param  string[]  $available
     * @return string[]
     */
    protected function resolveModuleSelection(array $available): array
    {
        if ($this->option('all-modules')) {
            return $available;
        }

        if ($input = $this->option('modules')) {
            $requested = array_map('strtolower', array_map('trim', explode(',', (string) $input)));

            return array_values(array_filter($available, fn (string $m) => in_array($m, $requested)));
        }

        if (! $this->input->isInteractive()) {
            return $available;
        }

        /** @var string[] $selection */
        $selection = $this->choice(
            'Which modules would you like to enable?',
            $available,
            implode(',', $available),
            attempts: null,
            multiple: true,
        );

        return $selection;
    }

    protected function createStorageLink(): void
    {
        $this->components->task('Creating storage link', function () {
            return Artisan::call('storage:link') === 0;
        });
    }

    protected function clearCaches(): void
    {
        $this->components->task('Clearing caches', function () {
            return Artisan::call('optimize:clear') === 0;
        });
    }

    protected function isCI(): bool
    {
        return ! empty(getenv('CI'))
            || ! empty(getenv('GITHUB_ACTIONS'))
            || ! empty(getenv('GITLAB_CI'))
            || ! empty(getenv('CIRCLECI'))
            || ! empty(getenv('TRAVIS'));
    }

    protected function displayWelcome(): void
    {
        $this->newLine();
        $this->line('  ┌───────────────────────────────────────┐');
        $this->line('  │                                       │');
        $this->line('  │       🍯 <fg=#5455c4;options=bold>SAUCE</><fg=#26b9d9;options=bold>BASE</> <fg=yellow;options=bold>INSTALLER</> 🍯       │');
        $this->line('  │                                       │');
        $this->line('  │   Laravel Modular SaaS Starter Kit    │');
        $this->line('  │                                       │');
        $this->line('  └───────────────────────────────────────┘');
        $this->newLine();
    }

    protected function displaySuccess(): void
    {
        $this->newLine();
        $this->info('Installation complete!');
        $this->newLine();
        $this->line('Next steps:');
        $this->line('  1. Ensure <fg=yellow>APP_URL</> is set correctly in <fg=yellow>.env</>');
        $this->line('  2. Run: <fg=yellow>npm install && npm run dev</>');
        $this->line('  3. Open your app in the browser');
        $this->newLine();
        $this->line('Learn more: <fg=cyan>https://github.com/sauce-base/saucebase</>');
    }
}
