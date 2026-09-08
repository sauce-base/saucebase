<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Ssr\SsrState;
use InertiaUI\Modal\Modal;

class ModalServiceProvider extends ServiceProvider
{
    /**
     * Keep the modal's own URL in the page payload when a modal URL is opened directly.
     *
     * Inertia Modal rewrites the URL by mutating the `page` view data, which the
     * `@inertia` directive used to read. Inertia v3 renders the payload from the
     * `SsrState` singleton instead, so that rewrite never reaches the HTML and the
     * front-end sees the base route's URL. It then decides the modal does not belong
     * to the current page and closes it, leaving a deep link showing the bare base
     * page. Patching the same value on `SsrState` restores the intended behaviour.
     *
     * Remove this provider once inertiaui/modal writes the base re-render URL
     * through SsrState.
     *
     * @see https://github.com/inertiaui/modal/blob/3.x/src/Modal.php `toViewResponse()`
     */
    public function boot(): void
    {
        if (! class_exists(Modal::class)) {
            return;
        }

        Modal::beforeBaseRerender(function ($request): void {
            $state = $this->app->make(SsrState::class);

            if ($state->page === []) {
                return;
            }

            $state->page['url'] = Str::start(
                Str::after($request->fullUrl(), $request->getSchemeAndHttpHost()),
                '/',
            );
        });
    }
}
