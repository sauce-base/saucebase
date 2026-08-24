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
     * A locale the admin has since turned off is ignored, which stops a stale session or
     * user record from pinning a visitor to a retired language.
     *
     * Web requests only. Mail and notifications go through `User::preferredLocale()`,
     * which Laravel reads itself, including for queued sends.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = app(LocalizationSettings::class);
        $enabledLocales = $settings->enabled();

        Inertia::share('locales', $enabledLocales);

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
