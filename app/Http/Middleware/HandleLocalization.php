<?php

namespace App\Http\Middleware;

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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = config('app.available_locales', []);

        Inertia::share('locales', $availableLocales);

        if ($locale = Session::get('locale')) {

            if (in_array($locale, array_keys($availableLocales))) {
                App::setLocale($locale);
            }
        }

        return $next($request);
    }
}
