<?php

namespace App\Http\Middleware;

use App\Services\Navigation;
use Closure;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Inertia\Middleware;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\HttpFoundation\Response;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * Handle the incoming request.
     *
     * Disables SSR by default for each request.
     * Controllers can opt-in using ->withSSR() or explicitly disable using ->withoutSSR()
     *
     * For Octane compatibility, SSR preferences are stored in request attributes
     * to avoid race conditions. The macros set both the attribute and config.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Disable SSR by default for this request
        // Store in request attributes (thread-safe for Octane)
        $request->attributes->set('inertia.ssr', false);
        Config::set('inertia.ssr.enabled', false);

        // Process request - controller may override SSR via macros
        return parent::handle($request, $next);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'locale' => app()->getLocale(),
            'modules' => fn () => collect(Module::allEnabled())
                ->mapWithKeys(fn ($module, $key) => [$key => $module->getName()])
                ->all(),
            'navigation' => fn () => app(Navigation::class)->treeGrouped(),
            'breadcrumbs' => $this->getBreadcrumbs(),
            'toast' => fn () => $request->session()->pull('toast'),
            // Ziggy data is computed lazily so it can be skipped on partial reloads
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ]);
    }

    /**
     * Get breadcrumbs for the current request.
     * Uses diglactic/laravel-breadcrumbs if available, falls back to Spatie Navigation.
     */
    protected function getBreadcrumbs(): array
    {
        $routeName = request()->route()?->getName();

        // Try to use laravel-breadcrumbs package
        if ($routeName && Breadcrumbs::exists($routeName)) {
            try {
                $breadcrumbs = Breadcrumbs::generate($routeName, ...request()->route()->parameters());

                // Convert to array format compatible with frontend
                return collect($breadcrumbs)->map(fn (\stdClass $crumb) => [
                    'title' => 'breadcrumbs.'.$crumb->title,
                    'url' => $crumb->url,
                    'attributes' => $crumb->data ?? [],
                ])->toArray();
            } catch (\Exception $e) {
                report($e);
                // Fall through to Spatie Navigation on error
            }
        }

        // Fallback to Spatie Navigation
        return app(Navigation::class)->breadcrumbs();
    }
}
