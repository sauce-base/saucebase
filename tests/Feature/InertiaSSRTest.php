<?php

namespace Tests\Feature;

use App\Services\FrontendConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Tests\TestCase;

class InertiaSSRTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // No longer needed - middleware now handles default disabling
        // Config::set('inertia.ssr.enabled', false);
    }

    public function test_ssr_config_is_enabled_for_server(): void
    {
        // Config must be true for SSR server to start
        // Middleware disables it per-request for opt-in behavior
        $this->assertTrue(config('inertia.ssr.enabled'));
    }

    public function test_with_ssr_macro_enables_ssr(): void
    {
        // Create an Inertia response and call withSSR()
        $response = Inertia::render('Index')->withSSR();

        // Verify SSR was enabled
        $this->assertTrue(config('inertia.ssr.enabled'));

        // Verify it returns the response (chainable)
        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_inertia_response_without_with_ssr_keeps_ssr_disabled(): void
    {
        // Simulate middleware having disabled SSR
        Config::set('inertia.ssr.enabled', false);

        // Create an Inertia response without calling withSSR()
        Inertia::render('Dashboard');

        // Verify SSR is still disabled
        $this->assertFalse(config('inertia.ssr.enabled'));
    }

    public function test_with_ssr_is_chainable_with_other_methods(): void
    {
        $response = Inertia::render('Index')
            ->with('foo', 'bar')
            ->withSSR()
            ->with('baz', 'qux');

        // Verify SSR was enabled
        $this->assertTrue(config('inertia.ssr.enabled'));

        // Verify it's still a proper Inertia response
        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_home_page_uses_ssr(): void
    {
        $this->assertTrue(config('inertia.ssr.enabled'), 'SSR should be enabled in config at boot');

        app()->bind(FrontendConfig::class, fn () => new class extends FrontendConfig
        {
            public function getFramework(): ?string
            {
                return 'vue';
            }
        });

        $this->get('/')->assertOk();

        $this->assertTrue(config('inertia.ssr.enabled'), 'SSR should be enabled after ->withSSR()');
    }

    public function test_without_ssr_macro_disables_ssr(): void
    {
        // Start with SSR enabled
        Config::set('inertia.ssr.enabled', true);

        // Create an Inertia response and call withoutSSR()
        $response = Inertia::render('Dashboard')->withoutSSR();

        // Verify SSR was disabled
        $this->assertFalse(config('inertia.ssr.enabled'));

        // Verify it returns the response (chainable)
        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_without_ssr_is_chainable_with_other_methods(): void
    {
        // Start with SSR enabled
        Config::set('inertia.ssr.enabled', true);

        $response = Inertia::render('Dashboard')
            ->with('foo', 'bar')
            ->withoutSSR()
            ->with('baz', 'qux');

        // Verify SSR was disabled
        $this->assertFalse(config('inertia.ssr.enabled'));

        // Verify it's still a proper Inertia response
        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_macros_can_override_each_other(): void
    {
        // Start with SSR disabled
        Config::set('inertia.ssr.enabled', false);

        // Enable, then disable
        Inertia::render('Index')->withSSR()->withoutSSR();
        $this->assertFalse(config('inertia.ssr.enabled'));

        // Disable, then enable
        Inertia::render('Index')->withoutSSR()->withSSR();
        $this->assertTrue(config('inertia.ssr.enabled'));
    }

    public function test_ssr_is_disabled_by_default_via_middleware(): void
    {
        // Verify SSR starts enabled in config (required for SSR server to run)
        $this->assertTrue(config('inertia.ssr.enabled'), 'SSR should be enabled in config at boot');

        // A route defined here rather than one of the application's own, so the test
        // stays about SSR. Real routes accumulate guards from whichever modules are
        // installed — /dashboard redirects once tenancy is present, for instance — and
        // this test should not fail for reasons that have nothing to do with SSR.
        Route::middleware('web')->get('/ssr-probe', fn () => Inertia::render('Index'));

        $response = $this->get('/ssr-probe');

        $response->assertOk();

        // After the middleware runs, SSR should be disabled
        // (since the controller doesn't call ->withSSR())
        $this->assertFalse(config('inertia.ssr.enabled'), 'Middleware should disable SSR by default');
    }
}
