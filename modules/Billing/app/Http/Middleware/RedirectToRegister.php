<?php

namespace Modules\Billing\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToRegister
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->guest()) {
            session()->put('url.intended', $request->fullUrl());

            return redirect()->route('register');
        }

        return $next($request);
    }
}
