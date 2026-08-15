import { expect, type Page } from '@playwright/test';
import type { Laravel } from '@saucebase/laravel-playwright';

type SessionCookie = {
    name: string;
    value: string;
    domain: string;
    path: string;
};

export async function loginAs(
    page: Page,
    laravel: Laravel,
    user: { email: string; password: string },
): Promise<void> {
    const cookie = await laravel.callFunction<SessionCookie>(
        'Tests\\Support\\AuthHelper::loginAs',
        [user.email],
    );

    await page.context().addCookies([
        {
            ...cookie,
            httpOnly: true,
            sameSite: 'Lax',
        },
    ]);
}

/**
 * Checks authentication via the `auth.user` Inertia prop (shared globally by
 * the auth module, and what the frontend itself reads — see
 * `resources/js/vue/components/Header.vue`) rather than the post-login
 * landing URL, which varies by which modules are installed (e.g. tenancy
 * redirects authenticated visitors away from `/dashboard`).
 *
 * `/api/v1/user` looks like the obvious check but isn't wired for
 * session-cookie auth in this app (`bootstrap/app.php` never registers
 * Sanctum's `EnsureFrontendRequestsAreStateful`, so it only accepts bearer
 * tokens) — it always 401s for a real browser session regardless of login
 * state.
 *
 * A `reload()` is required before reading the prop: Inertia's client-side
 * router updates its in-memory page state on navigation but does not rewrite
 * the `<script data-page="app">` tag's contents, so reading it right after an
 * in-page action (e.g. submitting the login form) would return stale,
 * pre-navigation data. `waitForLoadState('networkidle')` has to come first:
 * a login can trigger a whole chain of server-side redirects (e.g. tenancy
 * funnelling into a workspace) driven by Inertia's client-side router, and
 * reloading while that's still in flight reloads whatever URL the browser
 * happens to be on that instant — which, called too early, is still the
 * pre-login page.
 */
async function currentAuthUser(page: Page): Promise<unknown> {
    await page.waitForLoadState('networkidle');
    await page.reload();

    return page.evaluate(() => {
        const script = document.querySelector('script[data-page="app"]');
        const data = script ? JSON.parse(script.textContent ?? '{}') : {};

        return data?.props?.auth?.user ?? null;
    });
}

// `expect.poll` rather than a single check: right after an in-page action
// (e.g. clicking "log in") the redirect chain that follows — POST, then
// possibly another server-side redirect from a module reacting to the new
// session — may still be in flight, so the first reload can race it. Each
// attempt does a real network round trip (reload), not a cheap DOM check, so
// the default 5s budget is tight under local multi-worker parallelism —
// give it more room.
export async function expectAuthenticated(page: Page): Promise<void> {
    await expect.poll(() => currentAuthUser(page), { timeout: 15_000 }).not.toBeNull();
}

export async function expectGuest(page: Page): Promise<void> {
    await expect.poll(() => currentAuthUser(page), { timeout: 15_000 }).toBeNull();
}
