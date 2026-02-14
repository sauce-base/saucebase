<?php

namespace App\Console\Commands\SauceBase;

use Illuminate\Console\Command;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Process\Process;

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

        // Detect environment
        if ($this->isCI()) {
            return $this->handleCIInstallation();
        }

        // Interactive installation
        if ($this->input->isInteractive()) {
            return $this->handleInteractiveInstallation();
        }

        // Non-interactive with flags
        return $this->handleAutomatedInstallation();
    }

    protected function handleInteractiveInstallation(): int
    {
        // Prompt: Use Docker?
        $useDocker = $this->option('no-docker')
            ? false
            : $this->confirm('Use Docker for local development?', true);

        if ($useDocker) {
            $this->installWithDocker();
        } else {
            $this->installManually();
        }

        return self::SUCCESS;
    }

    protected function handleAutomatedInstallation(): int
    {
        // When --no-interaction is passed, use Docker by default unless --no-docker is specified
        if ($this->option('no-docker')) {
            $this->installManually();
        } else {
            $this->installWithDocker();
        }

        return self::SUCCESS;
    }

    protected function installWithDocker(): void
    {
        $this->info('🐳 Setting up with Docker...');
        $this->newLine();

        // Check requirements
        if (! $this->checkDockerRequirements()) {
            return;
        }

        // Check for uncommitted changes
        if (! $this->checkGitStatus()) {
            return;
        }

        // Check if ports are available
        if (! $this->option('force') && ! $this->checkPorts()) {
            return;
        }

        // Determine SSL setup
        $setupSsl = ! $this->option('no-ssl') && $this->shouldSetupSSL();

        // Configure environment
        $this->configureEnvironment($setupSsl);

        // Setup SSL certificates
        if ($setupSsl) {
            $this->setupSslCertificates();
        }

        // Stop existing containers
        $this->stopExistingContainers();

        // Build Docker images if forced
        if ($this->option('force')) {
            $this->resetDockerServices();
            $this->buildDockerImages();
        }

        // Start Docker services
        $this->startDockerServices();

        // Install PHP dependencies
        $this->installComposerDependencies();

        // Generate application key
        $this->generateApplicationKey();

        // Setup database
        $this->setupDatabase();

        // Install and setup modules (Auth, Settings)
        $this->setupModules();

        // Run module migrations
        $this->migrateModules();

        // Create storage link
        $this->createStorageLink();

        // Clear caches
        $this->clearCaches();

        // Install and build frontend
        $this->buildFrontend();

        // Verify installation
        $this->verifyInstallation();

        // Display success
        $this->displaySuccess();
    }

    protected function installManually(): void
    {
        $this->info('📝 Manual setup mode...');

        $this->components->task('Creating .env file', function () {
            // Already done by composer
            return true;
        });

        $this->components->task('Generating application key', function () {
            // Already done by composer
            return true;
        });

        $this->warn('You\'ll need to:');
        $this->line('  1. Configure your database in .env');
        $this->line('  2. Run: php artisan migrate');
        $this->line('  3. Run: npm install && npm run dev');
        $this->line('  4. Configure your web server');

        $this->newLine();
        $this->info('Documentation: https://sauce-base.github.io/docs/');
    }

    protected function handleCIInstallation(): int
    {
        $this->info('🤖 CI environment detected - running minimal setup...');

        // Just verify basics
        $this->components->task('Verifying .env', fn () => file_exists(base_path('.env')));
        $this->components->task('Verifying app key', fn () => ! empty(config('app.key')));

        $this->info('✓ CI setup complete');

        return self::SUCCESS;
    }

    protected function checkDockerRequirements(): bool
    {
        $requirements = [
            'Docker' => ['docker', '--version'],
            'Docker Compose' => ['docker', 'compose', 'version'],
            'Node.js' => ['node', '--version'],
            'npm' => ['npm', '--version'],
        ];

        $missing = [];

        foreach ($requirements as $name => $command) {
            $process = new Process($command);
            $process->run();

            if (! $process->isSuccessful()) {
                $missing[] = $name;
            }
        }

        if (! empty($missing)) {
            $this->error('Missing requirements: '.implode(', ', $missing));
            $this->line('');
            $this->line('Install required tools:');
            $this->line('  Docker: https://docs.docker.com/get-docker/');
            $this->line('  Node.js: https://nodejs.org/ (v18+)');
            $this->line('');
            $this->line('Then run: php artisan saucebase:install');

            return false;
        }

        // Check if Docker daemon is running
        $process = new Process(['docker', 'ps']);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Docker is installed but not running.');
            $this->line('Please start Docker Desktop and try again.');

            return false;
        }

        return true;
    }

    protected function checkPorts(): bool
    {
        $ports = [
            80 => 'HTTP (Nginx)',
            443 => 'HTTPS (Nginx)',
            3306 => 'MySQL',
            6379 => 'Redis',
            8025 => 'Mailpit',
        ];

        $portsInUse = [];

        foreach ($ports as $port => $service) {
            if ($this->isPortInUse($port)) {
                $portsInUse[$port] = $service;
            }
        }

        if (! empty($portsInUse)) {
            $this->error('The following ports are already in use:');
            $this->newLine();

            foreach ($portsInUse as $port => $service) {
                $this->line("  Port {$port}: {$service}");
            }

            $this->newLine();
            $this->line('Solutions:');
            $this->line('  1. Check for other running Docker containers: docker compose down -v --remove-orphans');
            $this->line('  2. Stop the services using these ports');
            $this->line('  3. Change ports in .env file');
            $this->newLine();
            $this->line('Then run: php artisan saucebase:install --force');

            return false;
        }

        return true;
    }

    protected function isPortInUse(int $port): bool
    {
        $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);

        if (is_resource($connection)) {
            fclose($connection);

            return true;
        }

        return false;
    }

    /**
     * Check if git repository has uncommitted changes
     */
    protected function checkGitStatus(): bool
    {
        // Check if git is available
        $gitCheck = new Process(['git', '--version']);
        $gitCheck->run();

        if (! $gitCheck->isSuccessful()) {
            return true; // Git not available, proceed without warning
        }

        // Check if we're in a git repository
        $isRepo = new Process(['git', 'rev-parse', '--is-inside-work-tree']);
        $isRepo->setWorkingDirectory(base_path());
        $isRepo->run();

        if (! $isRepo->isSuccessful()) {
            return true; // Not a git repo, proceed without warning
        }

        // Check for uncommitted changes
        $status = new Process(['git', 'status', '--porcelain']);
        $status->setWorkingDirectory(base_path());
        $status->run();

        $changes = trim($status->getOutput());

        if (! empty($changes) && $this->input->isInteractive()) {
            $this->newLine();
            $this->warn('You have uncommitted changes in your repository:');
            $this->newLine();

            // Show abbreviated status (first 10 files max)
            $lines = explode("\n", $changes);
            $shown = array_slice($lines, 0, 10);
            foreach ($shown as $line) {
                $this->line("  {$line}");
            }
            if (count($lines) > 10) {
                $this->line('  ... and '.(count($lines) - 10).' more files');
            }

            $this->newLine();

            if (! $this->confirm('The installation may modify files. Do you want to continue?', false)) {
                $this->newLine();
                $this->info('Installation cancelled. You can:');
                $this->line('  • Commit your changes:  <fg=yellow>git add . && git commit -m "WIP"</>');
                $this->line('  • Stash your changes:   <fg=yellow>git stash</>');
                $this->line('  • Discard your changes: <fg=yellow>git checkout .</>');
                $this->newLine();
                $this->line('Then run: <fg=yellow>php artisan saucebase:install</>');

                return false;
            }
        }

        return true;
    }

    protected function shouldSetupSSL(): bool
    {
        // Check if mkcert is installed
        $process = new Process(['which', 'mkcert']);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->warn('mkcert not found - SSL certificates will be skipped.');
            $this->line('Install mkcert for HTTPS: brew install mkcert');

            return false;
        }

        return $this->confirm('Generate SSL certificates for HTTPS?', true);
    }

    protected function dumpAutoload(): bool
    {
        $process = new Process(['docker', 'compose', 'exec', '-T', 'app', 'composer', 'dump-autoload']);
        $process->run();

        return $process->isSuccessful();
    }

    protected function setupModules(): void
    {
        $this->newLine();
        $this->info('📦 Installing required modules...');

        // Check if module directories already exist
        $existingModules = [];
        $moduleDirs = ['Auth', 'Settings'];

        foreach ($moduleDirs as $moduleName) {
            $modulePath = base_path("modules/{$moduleName}");
            if (is_dir($modulePath)) {
                $existingModules[] = $moduleName;
            }
        }

        // Prompt user if modules exist (interactive mode only)
        $skipModules = [];
        if (! empty($existingModules) && $this->input->isInteractive()) {
            $this->warn('The following module directories already exist:');
            foreach ($existingModules as $moduleName) {
                $this->line("  - modules/{$moduleName}/");
            }
            $this->newLine();

            if (! $this->confirm('Do you want to overwrite these modules? (No will keep existing files)', false)) {
                $skipModules = $existingModules;
                $this->info('Keeping existing module directories.');
            }
        }

        // Check if modules are already installed
        $composerJson = json_decode(file_get_contents(base_path('composer.json')), true);
        $requireDev = $composerJson['require-dev'] ?? [];

        $modulesToInstall = [];

        if (! in_array('Auth', $skipModules) && ! isset($requireDev['saucebase/auth']) && ! Module::has('Auth')) {
            $modulesToInstall[] = 'saucebase/auth';
        }

        if (! in_array('Settings', $skipModules) && ! isset($requireDev['saucebase/settings']) && ! Module::has('Settings')) {
            $modulesToInstall[] = 'saucebase/settings';
        }

        if (! empty($modulesToInstall)) {
            $this->components->task('Installing '.implode(', ', $modulesToInstall), function () use ($modulesToInstall) {
                $command = array_merge(
                    ['docker', 'compose', 'exec', '-T', 'app', 'composer', 'require', '--dev'],
                    $modulesToInstall
                );

                $process = new Process($command);
                $process->setTimeout(300);
                $process->run();

                return $process->isSuccessful();
            });

            // Dump autoload after installing modules
            $this->components->task('Updating autoloader', function () {
                return $this->dumpAutoload();
            });

            // Enable modules
            $this->components->task('Enabling modules', function () {
                $process = new Process(['docker', 'compose', 'exec', '-T', 'app', 'php', 'artisan', 'module:enable', '--all']);
                $process->run();

                return $process->isSuccessful();
            });

            // Note: Migrations are handled by migrateModules() which runs after this
        } else {
            $this->info('✓ Auth and Settings modules already installed');
        }
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

    protected function setupSslCertificates(): void
    {
        $this->components->task('Setting up SSL certificates', function () {
            $sslDir = base_path('docker/ssl');

            if (! is_dir($sslDir)) {
                mkdir($sslDir, 0755, true);
            }

            $certFile = $sslDir.'/app.pem';
            $keyFile = $sslDir.'/app.key.pem';

            if (file_exists($certFile) && file_exists($keyFile)) {
                return true; // Certificates already exist
            }

            // Install mkcert CA
            $process = new Process(['mkcert', '-install']);
            $process->run();

            // Generate certificates with wildcard support
            $process = new Process([
                'mkcert',
                '-key-file',
                $keyFile,
                '-cert-file',
                $certFile,
                '*.localhost',
                'localhost',
                '127.0.0.1',
                '::1',
            ], $sslDir);

            $process->run();

            return $process->isSuccessful();
        });
    }

    protected function stopExistingContainers(): void
    {
        $this->components->task('Stopping existing containers', function () {
            $process = new Process(['docker', 'compose', 'down', '-v', '--remove-orphans']);
            $process->run();

            return $process->isSuccessful();
        });
    }

    protected function buildDockerImages(): void
    {
        $this->info('Building Docker images...');

        $process = new Process(['docker', 'compose', 'pull']);
        $process->setTimeout(300);
        $process->run(function ($_, $buffer) {
            echo $buffer;
        });

        $process = new Process(['docker', 'compose', 'build', '--no-cache']);
        $process->setTimeout(600);
        $process->run(function ($_, $buffer) {
            echo $buffer;
        });
    }

    protected function resetDockerServices(): void
    {
        $this->components->task('Reseting Docker services', function () {
            $process = new Process(['docker', 'compose', 'down', '-v', '--remove-orphans']);
            $process->setTimeout(180);
            $process->run();

            return $process->isSuccessful();
        });
    }

    protected function startDockerServices(): void
    {
        $this->newLine();
        $this->info('🐳 Starting Docker services...');

        // Services in dependency order (databases first, then app, then web server)
        $services = [
            'mysql' => ['label' => 'MySQL database', 'healthcheck' => true, 'timeout' => 60],
            'redis' => ['label' => 'Redis cache', 'healthcheck' => true, 'timeout' => 30],
            'mailpit' => ['label' => 'Mailpit (email testing)', 'healthcheck' => false, 'timeout' => 15],
            'app' => ['label' => 'PHP-FPM application', 'healthcheck' => false, 'timeout' => 30],
            'nginx' => ['label' => 'Nginx web server', 'healthcheck' => false, 'timeout' => 15],
        ];

        foreach ($services as $name => $config) {
            $this->components->task("Starting {$config['label']}", function () use ($name, $config) {
                // Start the service
                $process = new Process(['docker', 'compose', 'up', '-d', $name]);
                $process->setTimeout(30);
                $process->run();

                if (! $process->isSuccessful()) {
                    return false;
                }

                // Wait for service to be ready
                return $this->waitForService($name, $config['healthcheck'], $config['timeout']);
            });
        }

        $this->newLine();
    }

    /**
     * Wait for a Docker service to be ready
     */
    protected function waitForService(string $serviceName, bool $hasHealthcheck, int $timeout): bool
    {
        $startTime = time();

        while ((time() - $startTime) < $timeout) {
            $process = new Process([
                'docker', 'compose', 'ps',
                '--format', '{{.State}}:{{.Health}}',
                $serviceName,
            ]);
            $process->run();

            $output = trim($process->getOutput());
            if (empty($output)) {
                sleep(1);

                continue;
            }

            $parts = explode(':', $output);
            $state = $parts[0];
            $health = $parts[1] ?? '';

            // Service is ready when running and (no healthcheck OR healthy)
            if ($state === 'running' && (! $hasHealthcheck || $health === 'healthy')) {
                return true;
            }

            sleep(1);
        }

        return false;
    }

    protected function installComposerDependencies(): void
    {
        $this->components->task('Installing PHP dependencies', function () {
            $process = new Process(['docker', 'compose', 'exec', '-T', 'app', 'composer', 'install']);
            $process->setTimeout(300);
            $process->run();

            return $process->isSuccessful();
        });
    }

    protected function generateApplicationKey(): void
    {
        $this->components->task('Generating application key', function () {
            // Check if key already exists
            $env = file_get_contents(base_path('.env'));
            if (preg_match('/^APP_KEY=base64:.+$/m', $env)) {
                return true; // Key already exists
            }

            $process = new Process(['docker', 'compose', 'exec', '-T', 'app', 'php', 'artisan', 'key:generate']);
            $process->run();

            if ($process->isSuccessful()) {
                // Restart containers to reload environment
                $restart = new Process(['docker', 'compose', 'restart', 'app']);
                $restart->run();

                // Wait for services to be ready
                sleep(5);

                return true;
            }

            return false;
        });
    }

    protected function setupDatabase(): void
    {
        $this->components->task('Setting up database', function () {
            $process = new Process(['docker', 'compose', 'exec', '-T', 'app', 'php', 'artisan', 'migrate:fresh', '--seed', '--force']);
            $process->setTimeout(300);
            $process->run();

            return $process->isSuccessful();
        });
    }

    protected function migrateModules(): void
    {
        $this->components->task('Migrating modules', function () {
            $process = new Process(['docker', 'compose', 'exec', '-T', 'app', 'php', 'artisan', 'module:migrate', '--all', '--seed']);
            $process->setTimeout(300);
            $process->run();

            return $process->isSuccessful();
        });
    }

    protected function createStorageLink(): void
    {
        $this->components->task('Creating storage link', function () {
            $process = new Process(['docker', 'compose', 'exec', '-T', 'app', 'php', 'artisan', 'storage:link']);
            $process->run();

            return $process->isSuccessful();
        });
    }

    protected function clearCaches(): void
    {
        $this->components->task('Clearing caches', function () {
            $process = new Process(['docker', 'compose', 'exec', '-T', 'app', 'php', 'artisan', 'optimize:clear']);
            $process->run();

            return $process->isSuccessful();
        });
    }

    protected function buildFrontend(): void
    {
        $this->newLine();
        $this->info('🎨 Building frontend assets...');
        $this->newLine();

        $this->components->task('Installing npm dependencies', function () {
            $process = new Process(['npm', 'install']);
            $process->setTimeout(300);
            $process->run();

            return $process->isSuccessful();
        });

        $this->components->task('Building assets', function () {
            $process = new Process(['npm', 'run', 'build']);
            $process->setTimeout(300);
            $process->run();

            return $process->isSuccessful();
        });

        $this->newLine();
    }

    protected function verifyInstallation(): void
    {
        $this->newLine();
        $this->info('🔍 Verifying installation...');

        $this->components->task('Database connection', function () {
            $process = new Process(['docker', 'compose', 'exec', '-T', 'app', 'php', 'artisan', 'migrate:status']);
            $process->run();

            return $process->isSuccessful();
        });

        $this->components->task('Application key', function () {
            $env = file_get_contents(base_path('.env'));

            return (bool) preg_match('/^APP_KEY=base64:.+$/m', $env);
        });

        $appUrl = config('app.url', 'https://localhost');
        $this->components->task('Web server', function () use ($appUrl) {
            $ch = curl_init($appUrl.'/health');
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
        $this->info('✓ Installation complete!');
        $this->newLine();
        $this->line('Your application is ready at: <fg=cyan>https://localhost</>');
        $this->newLine();
        $this->line('Next steps:');
        $this->line('  1. Run: <fg=yellow>npm run dev</>');
        $this->line('  2. Visit: https://localhost');
        $this->line('  3. Login with demo credentials (check seeders)');
        $this->newLine();
        $this->line('Learn more: <fg=cyan>https://github.com/sauce-base/saucebase</>');
    }
}
