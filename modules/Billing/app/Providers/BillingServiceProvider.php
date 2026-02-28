<?php

namespace Modules\Billing\Providers;

use App\Providers\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Billing\Console\ExpireCheckoutSessionsCommand;
use Modules\Billing\Contracts\PaymentGatewayInterface;
use Modules\Billing\Services\BillingService;
use Modules\Billing\Services\PaymentGatewayManager;

class BillingServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Billing';

    protected string $nameLower = 'billing';

    protected array $providers = [
        RouteServiceProvider::class,
    ];

    protected array $commands = [
        ExpireCheckoutSessionsCommand::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(PaymentGatewayManager::class);

        $this->app->bind(PaymentGatewayInterface::class, function ($app) {
            return $app->make(PaymentGatewayManager::class)->driver();
        });

        $this->app->singleton(BillingService::class);
    }

    public function boot(): void
    {
        parent::boot();

        $this->loadViewsFrom(module_path($this->name, 'resources/views'), $this->nameLower);

        // Register policies
        $this->registerPolicies();
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        parent::registerConfig();

        $this->mergeConfigFrom(module_path($this->name, 'config/services.php'), 'services');
        $this->replaceConfig('config/typescript-transformer.php', 'typescript-transformer');
    }

    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->command('billing:expire-checkout-sessions')->everyThirtyMinutes();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(
            \Modules\Billing\Models\Subscription::class,
            \Modules\Billing\Policies\SubscriptionPolicy::class
        );
    }
}
