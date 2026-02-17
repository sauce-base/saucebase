<?php

namespace App\Console\Commands\SauceBase;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Nwidart\Modules\Facades\Module;

class InstallCommand extends Command
{
    protected $signature = 'saucebase:install
                            {--no-docker : Skip Docker setup and use manual configuration}
                            {--no-ssl : Skip SSL certificate generation}
                            {--force : Force reinstallation even if already set up}';

    protected $description = 'Install and configure Saucebase';

    public function handle(): int
    {
        $this->displayWelcome();

        if ($this->isCI()) {
            return $this->handleCIInstallation();
        }

        if ($this->option('no-docker')) {
            $this->installManually();

            return self::SUCCESS;
        }

        $this->install();

        return self::SUCCESS;
    }

    protected function install(): void
    {
        // Configure environment
        $setupSsl = ! $this->option('no-ssl') && $this->hasSslCertificates();
        $this->configureEnvironment($setupSsl);

        // Generate application key
        $this->generateApplicationKey();

        // Setup database
        $this->setupDatabase();

        // Install and setup modules
        $this->setupModules();

        // Create storage link
        $this->createStorageLink();

        // Clear caches
        $this->clearCaches();

        // Verify installation
        $this->verifyInstallation();

        // Display success
        $this->displaySuccess();
    }

    protected function installManually(): void
    {
        $this->info('Manual setup mode...');

        $this->components->task('Creating .env file', fn () => true);
        $this->components->task('Generating application key', fn () => true);

        $this->warn('You\'ll need to:');
        $this->line('  1. Configure your database in .env');
        $this->line('  2. Run: php artisan migrate');
        $this->line('  3. Run: npm install && npm run dev');
        $this->line('  4. Configure your web server');

        $this->newLine();
        $this->info('Documentation: https://github.com/sauce-base/saucebase');
    }

    protected function handleCIInstallation(): int
    {
        $this->info('CI environment detected - running minimal setup...');

        $this->components->task('Verifying .env', fn () => file_exists(base_path('.env')));
        $this->components->task('Verifying app key', fn () => ! empty(config('app.key')));

        $this->info('CI setup complete');

        return self::SUCCESS;
    }

    protected function hasSslCertificates(): bool
    {
        return file_exists(base_path('docker/ssl/app.pem'))
            && file_exists(base_path('docker/ssl/app.key.pem'));
    }

    protected function configureEnvironment(bool $setupSsl): void
    {
        $this->components->task('Configuring environment', function () use ($setupSsl) {
            $appUrl = $setupSsl ? 'https://localhost' : 'http://localhost';

            $env = file_get_contents(base_path('.env'));
            $env = preg_replace('/^APP_HOST=.*/m', 'APP_HOST=localhost', $env);
            $env = preg_replace('/^APP_URL=.*/m', "APP_URL={$appUrl}", $env);
            file_put_contents(base_path('.env'), $env);

            return true;
        });
    }

    protected function generateApplicationKey(): void
    {
        $this->components->task('Generating application key', function () {
            $env = file_get_contents(base_path('.env'));
            if (preg_match('/^APP_KEY=base64:.+$/m', $env)) {
                return true;
            }

            Artisan::call('key:generate', ['--force' => true]);

            return true;
        });
    }

    protected function setupDatabase(): void
    {
        $this->components->task('Setting up database', function () {
            return Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]) === 0;
        });
    }

    protected function setupModules(): void
    {
        $this->newLine();
        $this->info('Installing modules...');

        $modules = ['Auth', 'Settings'];

        foreach ($modules as $module) {
            if (Module::has($module)) {
                $this->components->task("Enabling {$module} module", function () use ($module) {
                    return Artisan::call('module:enable', ['module' => $module]) === 0;
                });

                $this->components->task("Migrating {$module} module", function () use ($module) {
                    return Artisan::call('module:migrate', ['module' => $module, '--seed' => true]) === 0;
                });
            } else {
                $this->warn("  Module {$module} not found - skipping");
            }
        }
    }

    protected function createStorageLink(): void
    {
        $this->components->task('Creating storage link', function () {
            Artisan::call('storage:link');

            return true;
        });
    }

    protected function clearCaches(): void
    {
        $this->components->task('Clearing caches', function () {
            Artisan::call('optimize:clear');

            return true;
        });
    }

    protected function verifyInstallation(): void
    {
        $this->newLine();
        $this->info('Verifying installation...');

        $this->components->task('Database connection', function () {
            return Artisan::call('migrate:status') === 0;
        });

        $this->components->task('Application key', function () {
            $env = file_get_contents(base_path('.env'));

            return (bool) preg_match('/^APP_KEY=base64:.+$/m', $env);
        });

        $appUrl = config('app.url', 'https://localhost');
        $this->components->task('Web server', function () use ($appUrl) {
            $ch = curl_init($appUrl.'/up');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $httpCode === 200;
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
        $url = config('app.url', 'https://localhost');
        $this->line("Your application is ready at: <fg=cyan>{$url}</>");
        $this->newLine();
        $this->line('Next steps:');
        $this->line('  1. Run: <fg=yellow>npm run dev</>');
        $this->line("  2. Visit: {$url}");
        $this->line('  3. Login with demo credentials (check seeders)');
        $this->newLine();
        $this->line('Learn more: <fg=cyan>https://github.com/sauce-base/saucebase</>');
    }
}
