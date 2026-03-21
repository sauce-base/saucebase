<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureMagicLinkEnabled
{
    public function handle(Request $request, Closure $next): mixed
    {
        abort_unless(config('auth.magic_link.enabled', true), 404);

        return $next($request);
    }
}
