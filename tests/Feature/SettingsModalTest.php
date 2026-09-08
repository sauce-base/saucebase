<?php

namespace Tests\Feature;

use App\Settings\SectionRegistry;
use App\Settings\SettingsSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Tests\TestCase;

class SettingsModalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Core ships no sections of its own — every one comes from a module — so these
     * use fixtures rather than whichever modules happen to be installed. That keeps
     * the suite meaningful on bare core, and keeps it testing the controller and the
     * registry rather than a module's data.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance(SectionRegistry::class, new class extends SectionRegistry
        {
            public function __construct() {}

            public function all(): Collection
            {
                return collect([
                    new AlphaSection,
                    new BetaSection,
                ]);
            }
        });
    }

    /**
     * Asserted on the middleware rather than by making the request: where the
     * unauthenticated visitor is *sent* is the auth module's business, and core has
     * no login route of its own to be redirected to.
     */
    public function test_the_settings_route_is_behind_authentication(): void
    {
        $middleware = Route::getRoutes()->getByName('settings')->gatherMiddleware();

        $this->assertContains('auth', $middleware);
    }

    public function test_it_renders_the_settings_modal_component(): void
    {
        $response = $this->actingAs($this->createUser())
            ->withHeader('X-InertiaUI-Modal', 'true')
            ->get(route('settings'))
            ->assertOk();

        $this->assertSame('Settings', $response->viewData('page')['component']);
    }

    public function test_it_defaults_to_the_first_registered_section(): void
    {
        $response = $this->actingAs($this->createUser())
            ->withHeader('X-InertiaUI-Modal', 'true')
            ->get(route('settings'))
            ->assertOk();

        $this->assertSame('alpha', $response->viewData('page')['props']['activeSection']);
    }

    public function test_it_resolves_props_for_the_requested_section(): void
    {
        $response = $this->actingAs($this->createUser())
            ->withHeader('X-InertiaUI-Modal', 'true')
            ->get(route('settings', ['section' => 'beta']))
            ->assertOk();

        $props = $response->viewData('page')['props'];

        $this->assertSame('beta', $props['activeSection']);
        $this->assertSame(['value' => 'beta'], $props['sectionProps']['beta']);
    }

    /**
     * Only the section being shown pays for its data; the rest are deferred, so
     * opening the modal never runs every other section's queries.
     */
    public function test_it_defers_the_sections_that_are_not_shown(): void
    {
        $response = $this->actingAs($this->createUser())
            ->withHeader('X-InertiaUI-Modal', 'true')
            ->get(route('settings', ['section' => 'beta']))
            ->assertOk();

        $this->assertArrayNotHasKey('alpha', $response->viewData('page')['props']['sectionProps']);
    }

    /**
     * The sidebar gets what it needs to list the sections, and nothing else.
     *
     * A section's props are resolved server-side and arrive as modal props, so a
     * section object must never travel to the front-end — this pins the shape that
     * does.
     */
    public function test_it_shares_only_the_sidebar_view_of_each_section(): void
    {
        $response = $this->actingAs($this->createUser())->get(route('index'));

        $sections = $response->inertiaProps('settings.sections');

        $this->assertSame(['slug', 'title', 'icon', 'component'], array_keys($sections[0]));
        $this->assertSame(
            [['alpha', 'Test::Alpha'], ['beta', 'Test::Beta']],
            array_map(fn (array $s): array => [$s['slug'], $s['component']], $sections),
        );
    }

    /**
     * Guards the SsrState fix in AppServiceProvider.
     *
     * Nothing in the app uses `baseRoute()` today, but the package ships it as a
     * headline feature, so a downstream modal that opts in must render its own
     * URL rather than the base route's.
     */
    public function test_a_modal_with_a_base_route_renders_its_own_url(): void
    {
        Route::middleware(['web', 'auth'])->get(
            '/__base-route-modal',
            fn () => Inertia::modal('Settings', [
                'activeSection' => null,
                'sectionProps' => [],
            ])->baseRoute('index'),
        );

        $html = $this->actingAs($this->createUser())
            ->get('/__base-route-modal')
            ->assertOk()
            ->getContent();

        $page = $this->renderedPage($html);

        // The base route supplies the component behind the modal...
        $this->assertSame('Index', $page['component']);

        // ...while the URL stays the modal's, which is what tells the front-end
        // the modal belongs to this page instead of closing it.
        $this->assertSame('/__base-route-modal', $page['url']);
        $this->assertSame('Settings', $page['props']['_inertiaui_modal']['component']);
    }

    /**
     * The page payload as the browser receives it.
     *
     * Deliberately not `viewData('page')`: that is the array the modal package
     * itself rewrites, so reading it would pass whether or not the rewrite ever
     * reaches the HTML — which under Inertia v3 it does not, because the payload
     * is rendered from `SsrState`. Asserting on the markup is the only way this
     * test can fail when the fix is removed.
     *
     * @return array<string, mixed>
     */
    private function renderedPage(string $html): array
    {
        preg_match('#<script[^>]*data-page="app"[^>]*>(.*?)</script>#s', $html, $matches);

        return json_decode(html_entity_decode($matches[1] ?? '{}'), true);
    }
}

class AlphaSection extends SettingsSection
{
    public function slug(): string
    {
        return 'alpha';
    }

    public function title(): string
    {
        return 'Alpha';
    }

    public function icon(): ?string
    {
        return 'alpha';
    }

    public function order(): int
    {
        return 10;
    }

    public function component(): string
    {
        return 'Test::Alpha';
    }

    public function props(): array
    {
        return ['value' => 'alpha'];
    }
}

class BetaSection extends AlphaSection
{
    public function slug(): string
    {
        return 'beta';
    }

    public function title(): string
    {
        return 'Beta';
    }

    public function icon(): ?string
    {
        return 'beta';
    }

    public function order(): int
    {
        return 20;
    }

    public function component(): string
    {
        return 'Test::Beta';
    }

    public function props(): array
    {
        return ['value' => 'beta'];
    }
}
