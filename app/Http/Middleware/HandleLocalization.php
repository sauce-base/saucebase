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
     * This covers web requests, which is the Inertia app and the Filament panel. Mail and
     * notifications are handled elsewhere, by `User::preferredLocale()` — Laravel reads
     * that contract itself, including for queued sends.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = app(LocalizationSettings::class);
        $enabledLocales = $settings->enabled();

        Inertia::share('locales', $enabledLocales);

        // A signed-in user's stored choice outranks the session, so logging in somewhere
        // new brings their language with them rather than inheriting whatever that browser
        // last looked at. Switching writes both, so the two rarely disagree.
        $locale = $request->user()->locale ?? Session::get('locale');

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
