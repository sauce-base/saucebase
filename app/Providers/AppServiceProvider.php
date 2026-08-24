<?php

namespace App\Providers;

use App\Http\Middleware\SecureHeaders;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Foundation\Events\LocaleUpdated;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use InterNACHI\Modular\Support\ModuleRegistry;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureSecureUrls();
        $this->configureFilamentDefaults();
        $this->addCommandAboutInfo();
        $this->keepDatesInTheApplicationLanguage();
    }

    /**
     * Teach Carbon which language the application is currently speaking.
     *
     * Laravel does not do this itself: `App::setLocale()` moves the translator and leaves
     * Carbon behind, so a date rendered with `isoFormat()` keeps its English month names
     * in a Portuguese email. Listening for the event rather than setting it in middleware
     * covers the paths a request never touches — queued mail and notifications, which set
     * the locale themselves from the recipient's `preferredLocale()`.
     */
    private function keepDatesInTheApplicationLanguage(): void
    {
        Carbon::setLocale($this->app->getLocale());

        Event::listen(LocaleUpdated::class, function (LocaleUpdated $event): void {
            Carbon::setLocale($event->locale);
        });
    }

    /**
     * How Filament components look before anybody asks.
     *
     * A toggle that is on is reporting a state, not offering the primary action on the
     * page, and `primary` is the brand colour every button already wears. Green says
     * "enabled" without competing with them.
     *
     * `configureUsing` runs at `make()`, so it is a default rather than an override: any
     * component that names its own `onColor` still wins. Registered here rather than in a
     * panel provider because these closures are recorded against the component class and
     * apply to every panel regardless of where they are declared — putting them in one
     * panel would only disguise that.
     */
    protected function configureFilamentDefaults(): void
    {
        Toggle::configureUsing(fn (Toggle $toggle) => $toggle->onColor('success'));

        ToggleColumn::configureUsing(fn (ToggleColumn $column) => $column->onColor('success'));
    }

    protected function configureSecureUrls(): void
    {
        // Determine if HTTPS should be enforced
        $enforceHttps = $this->app->environment(['production', 'staging'])
            && ! $this->app->runningUnitTests();

        // For local development with SSL setup
        $localHttps = $this->app->environment('local')
            && config('app.url')
            && str_starts_with(config('app.url'), 'https://')
            && ! $this->app->runningUnitTests();

        $useHttps = $enforceHttps || $localHttps;

        // Force HTTPS for all generated URLs
        URL::forceHttps($useHttps);

        // Ensure proper server variable is set
        if ($useHttps) {
            $this->app['request']->server->set('HTTPS', 'on');
        }

        // Set up global middleware for security headers in production/staging
        if ($enforceHttps) {
            $this->app['router']->pushMiddlewareToGroup('web', SecureHeaders::class);
        }
    }

    protected function addCommandAboutInfo(): void
    {
        AboutCommand::add(
            'Modules',
            app(ModuleRegistry::class)->modules()->mapWithKeys(fn ($module) => [
                $module->name => fn () => module_path($module->name),
            ])->toArray(),
        );
    }
}
