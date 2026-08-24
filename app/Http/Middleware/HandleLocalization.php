<?php

namespace App\Http\Middleware;

use App\Settings\LocalizationSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class HandleLocalization
{
    /**
     * Handle an incoming request.
     *
     * Which languages exist is a code question; which of them the application offers is an
     * admin one, so both the shared prop and the session check read the setting rather
     * than config. A locale the admin has since turned off is ignored, which is what stops
     * a stale session from pinning a visitor to a retired language.
     *
     * ponytail: web requests only, which covers the Inertia app and the Filament panel.
     * Queued jobs and mail still resolve `config('app.locale')`; wire them through the
     * setting when that becomes a real complaint.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = app(LocalizationSettings::class);
        $enabledLocales = $settings->enabled();

        Inertia::share('locales', $enabledLocales);

        $locale = Session::get('locale');

        App::setLocale(
            is_string($locale) && array_key_exists($locale, $enabledLocales)
                ? $locale
                : $this->defaultLocale($settings, $enabledLocales)
        );

        return $next($request);
    }

    /**
     * The locale to fall back on, guaranteed to be one the application still offers.
     *
     * @param  array<string, string>  $enabledLocales
     */
    private function defaultLocale(LocalizationSettings $settings, array $enabledLocales): string
    {
        if (array_key_exists($settings->default_locale, $enabledLocales)) {
            return $settings->default_locale;
        }

        return (string) array_key_first($enabledLocales);
    }
}
