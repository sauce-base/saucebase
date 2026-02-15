<?php

namespace Tests\Unit\Services;

use App\Facades\Navigation as NavigationFacade;
use App\Navigation\Section;
use App\Services\Navigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Navigation\Helpers\ActiveUrlChecker;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    private Navigation $navigation;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a fresh Navigation instance without auto-loading route files
        $this->navigation = new Navigation(app(ActiveUrlChecker::class));
    }

    public function test_add_registers_navigation_item(): void
    {
        $this->navigation->add('Dashboard', '/dashboard', function (Section $section) {
            $section->attributes(['group' => 'main']);
        });

        $tree = $this->navigation->tree();

        $this->assertCount(1, $tree);
        $this->assertEquals('Dashboard', $tree[0]['title']);
        $this->assertStringContainsString('/dashboard', $tree[0]['url']);
    }

    public function test_add_when_includes_item_when_condition_is_true(): void
    {
        $this->navigation->addWhen(
            fn () => true,
            'Visible',
            '/visible',
            function (Section $section) {
                $section->attributes(['group' => 'main']);
            }
        );

        $grouped = $this->navigation->treeGrouped();

        $this->assertArrayHasKey('main', $grouped);
        $this->assertCount(1, $grouped['main']);
        $this->assertEquals('Visible', $grouped['main'][0]['title']);
    }

    public function test_add_when_excludes_item_when_condition_is_false(): void
    {
        $this->navigation->addWhen(
            fn () => false,
            'Hidden',
            '/hidden',
            function (Section $section) {
                $section->attributes(['group' => 'main']);
            }
        );

        $grouped = $this->navigation->treeGrouped();

        $this->assertEmpty($grouped['main'] ?? []);
    }

    public function test_tree_grouped_groups_items_by_group_attribute(): void
    {
        $this->navigation->add('Dashboard', '/dashboard', function (Section $section) {
            $section->attributes(['group' => 'main']);
        });

        $this->navigation->add('Settings', '/settings', function (Section $section) {
            $section->attributes(['group' => 'settings']);
        });

        $this->navigation->add('Docs', '/docs', function (Section $section) {
            $section->attributes(['group' => 'main']);
        });

        $grouped = $this->navigation->treeGrouped();

        $this->assertArrayHasKey('main', $grouped);
        $this->assertArrayHasKey('settings', $grouped);
        $this->assertCount(2, $grouped['main']);
        $this->assertCount(1, $grouped['settings']);
    }

    public function test_tree_grouped_uses_ungrouped_for_items_without_group(): void
    {
        $this->navigation->add('Orphan', '/orphan');

        $grouped = $this->navigation->treeGrouped();

        $this->assertArrayHasKey('ungrouped', $grouped);
        $this->assertCount(1, $grouped['ungrouped']);
    }

    public function test_items_are_sorted_by_order_attribute(): void
    {
        $this->navigation->add('Third', '/third', function (Section $section) {
            $section->attributes(['group' => 'main', 'order' => 30]);
        });

        $this->navigation->add('First', '/first', function (Section $section) {
            $section->attributes(['group' => 'main', 'order' => 10]);
        });

        $this->navigation->add('Second', '/second', function (Section $section) {
            $section->attributes(['group' => 'main', 'order' => 20]);
        });

        $grouped = $this->navigation->treeGrouped();

        $this->assertEquals('First', $grouped['main'][0]['title']);
        $this->assertEquals('Second', $grouped['main'][1]['title']);
        $this->assertEquals('Third', $grouped['main'][2]['title']);
    }

    public function test_transform_item_produces_correct_structure(): void
    {
        $this->navigation->add('Dashboard', '/dashboard', function (Section $section) {
            $section->attributes([
                'group' => 'main',
                'slug' => 'dashboard',
                'order' => 0,
            ]);
        });

        $grouped = $this->navigation->treeGrouped();
        $item = $grouped['main'][0];

        $this->assertArrayHasKey('title', $item);
        $this->assertArrayHasKey('active', $item);
        $this->assertArrayHasKey('slug', $item);
        $this->assertArrayHasKey('url', $item);
        $this->assertEquals('Dashboard', $item['title']);
        $this->assertEquals('dashboard', $item['slug']);
        $this->assertFalse($item['active']);
    }

    public function test_transform_item_includes_optional_attributes(): void
    {
        $this->navigation->add('Admin', '/admin', function (Section $section) {
            $section->attributes([
                'group' => 'main',
                'external' => true,
                'newPage' => true,
                'class' => 'text-red-500',
                'badge' => ['content' => 'New', 'variant' => 'info'],
            ]);
        });

        $grouped = $this->navigation->treeGrouped();
        $item = $grouped['main'][0];

        $this->assertTrue($item['external']);
        $this->assertTrue($item['newPage']);
        $this->assertEquals('text-red-500', $item['class']);
        $this->assertEquals(['content' => 'New', 'variant' => 'info'], $item['badge']);
    }

    public function test_transform_item_excludes_internal_attributes(): void
    {
        $this->navigation->addWhen(
            fn () => true,
            'Item',
            '/item',
            function (Section $section) {
                $section->attributes([
                    'group' => 'main',
                    'order' => 5,
                ]);
            }
        );

        $grouped = $this->navigation->treeGrouped();
        $item = $grouped['main'][0];

        $this->assertArrayNotHasKey('when', $item);
        $this->assertArrayNotHasKey('group', $item);
        $this->assertArrayNotHasKey('order', $item);
    }

    public function test_transform_item_generates_slug_from_title(): void
    {
        $this->navigation->add('Star us on Github', '/github', function (Section $section) {
            $section->attributes(['group' => 'main']);
        });

        $grouped = $this->navigation->treeGrouped();

        $this->assertEquals('star-us-on-github', $grouped['main'][0]['slug']);
    }

    public function test_is_item_active_returns_false_when_no_url(): void
    {
        $this->navigation->add('No URL', '', function (Section $section) {
            $section->attributes(['group' => 'main']);
        });

        $grouped = $this->navigation->treeGrouped();

        $this->assertFalse($grouped['main'][0]['active']);
    }

    public function test_children_are_transformed_recursively(): void
    {
        $this->navigation->add('Parent', '/parent', function (Section $section) {
            $section->attributes(['group' => 'main', 'order' => 0]);

            $section->add('Child', '/child', function (Section $child) {
                $child->attributes(['order' => 0]);
            });
        });

        $grouped = $this->navigation->treeGrouped();
        $parent = $grouped['main'][0];

        $this->assertArrayHasKey('children', $parent);
        $this->assertCount(1, $parent['children']);
        $this->assertEquals('Child', $parent['children'][0]['title']);
        $this->assertArrayHasKey('slug', $parent['children'][0]);
    }

    public function test_facade_resolves_to_custom_navigation(): void
    {
        $resolved = NavigationFacade::getFacadeRoot();

        $this->assertInstanceOf(Navigation::class, $resolved);
    }

    public function test_filter_callable_removes_children_with_false_condition(): void
    {
        $this->navigation->add('Parent', '/parent', function (Section $section) {
            $section->attributes(['group' => 'main']);

            $section->add('Visible Child', '/visible', function (Section $child) {
                $child->attributes(['when' => fn () => true]);
            });

            $section->add('Hidden Child', '/hidden', function (Section $child) {
                $child->attributes(['when' => fn () => false]);
            });
        });

        $grouped = $this->navigation->treeGrouped();
        $parent = $grouped['main'][0];

        $this->assertCount(1, $parent['children']);
        $this->assertEquals('Visible Child', $parent['children'][0]['title']);
    }

    // --- isItemActive (URL matching) ---

    public function test_is_item_active_returns_true_for_exact_url_match(): void
    {
        $this->app->instance('request', Request::create('http://localhost/dashboard'));

        $this->navigation->add('Dashboard', 'http://localhost/dashboard', function (Section $section) {
            $section->attributes(['group' => 'main']);
        });

        $grouped = $this->navigation->treeGrouped();

        $this->assertTrue($grouped['main'][0]['active']);
    }

    public function test_is_item_active_returns_false_for_different_url(): void
    {
        $this->app->instance('request', Request::create('http://localhost/settings'));

        $this->navigation->add('Dashboard', 'http://localhost/dashboard', function (Section $section) {
            $section->attributes(['group' => 'main']);
        });

        $grouped = $this->navigation->treeGrouped();

        $this->assertFalse($grouped['main'][0]['active']);
    }

    public function test_is_item_active_normalizes_trailing_slashes(): void
    {
        $this->app->instance('request', Request::create('http://localhost/dashboard/'));

        $this->navigation->add('Dashboard', 'http://localhost/dashboard', function (Section $section) {
            $section->attributes(['group' => 'main']);
        });

        $grouped = $this->navigation->treeGrouped();

        $this->assertTrue($grouped['main'][0]['active']);
    }

    // --- addWhen edge cases ---

    public function test_add_when_without_configure_callback(): void
    {
        $this->navigation->addWhen(fn () => true, 'Item', '/item');

        $tree = $this->navigation->tree();

        $this->assertCount(1, $tree);
        $this->assertEquals('Item', $tree[0]['title']);
    }

    public function test_add_when_is_fluent(): void
    {
        $result = $this->navigation->addWhen(fn () => true, 'Item', '/item');

        $this->assertSame($this->navigation, $result);
    }

    public function test_add_when_mixed_conditions_in_same_group(): void
    {
        $this->navigation->addWhen(fn () => true, 'Visible', '/visible', function (Section $section) {
            $section->attributes(['group' => 'main']);
        });

        $this->navigation->addWhen(fn () => false, 'Hidden', '/hidden', function (Section $section) {
            $section->attributes(['group' => 'main']);
        });

        $this->navigation->addWhen(fn () => true, 'Also Visible', '/also-visible', function (Section $section) {
            $section->attributes(['group' => 'main']);
        });

        $grouped = $this->navigation->treeGrouped();

        $this->assertCount(2, $grouped['main']);
        $this->assertEquals('Visible', $grouped['main'][0]['title']);
        $this->assertEquals('Also Visible', $grouped['main'][1]['title']);
    }

    // --- load() method ---

    public function test_load_returns_self(): void
    {
        $result = $this->navigation->load();

        $this->assertSame($this->navigation, $result);
    }

    public function test_load_loads_core_navigation_file(): void
    {
        // Swap the container binding to our fresh test instance and use include
        // (not require_once) to reliably re-execute the navigation file
        NavigationFacade::clearResolvedInstances();
        $this->app->instance(Navigation::class, $this->navigation);

        include base_path('routes/navigation.php');

        $grouped = $this->navigation->treeGrouped();

        $this->assertArrayHasKey('main', $grouped);

        $titles = array_column($grouped['main'], 'title');
        $this->assertContains('Dashboard', $titles);
    }

    public function test_load_skips_disabled_modules(): void
    {
        // Temporarily override modules_statuses.json with all modules disabled
        $statusPath = base_path('modules_statuses.json');
        $original = file_get_contents($statusPath);

        file_put_contents($statusPath, json_encode([
            'Auth' => false,
            'Settings' => false,
            'Billing' => false,
        ]));

        try {
            $navigation = new Navigation(app(ActiveUrlChecker::class));
            $navigation->load();

            $grouped = $navigation->treeGrouped();

            // Settings group should not exist (only registered by Settings module)
            $this->assertArrayNotHasKey('settings', $grouped);
        } finally {
            file_put_contents($statusPath, $original);
        }
    }

    // --- Service provider integration ---

    public function test_container_resolves_custom_navigation_class(): void
    {
        $resolved = app(Navigation::class);

        $this->assertInstanceOf(Navigation::class, $resolved);
    }

    public function test_spatie_alias_resolves_to_custom_class(): void
    {
        $resolved = app(\Spatie\Navigation\Navigation::class);

        $this->assertInstanceOf(Navigation::class, $resolved);
    }

    public function test_scoped_binding_returns_same_instance_within_request(): void
    {
        $first = app(Navigation::class);
        $second = app(Navigation::class);

        $this->assertSame($first, $second);
    }

    // --- Edge cases ---

    public function test_empty_navigation_returns_empty_grouped(): void
    {
        $grouped = $this->navigation->treeGrouped();

        $this->assertEmpty($grouped);
    }

    public function test_items_without_order_default_to_last(): void
    {
        $this->navigation->add('No Order', '/no-order', function (Section $section) {
            $section->attributes(['group' => 'main']);
        });

        $this->navigation->add('Ordered', '/ordered', function (Section $section) {
            $section->attributes(['group' => 'main', 'order' => 10]);
        });

        $grouped = $this->navigation->treeGrouped();

        $this->assertEquals('Ordered', $grouped['main'][0]['title']);
        $this->assertEquals('No Order', $grouped['main'][1]['title']);
    }

    public function test_action_item_with_hash_url(): void
    {
        $this->app->instance('request', Request::create('http://localhost/dashboard'));

        $this->navigation->add('Log out', '#', function (Section $section) {
            $section->attributes([
                'group' => 'user',
                'action' => 'logout',
                'slug' => 'logout',
            ]);
        });

        $grouped = $this->navigation->treeGrouped();
        $item = $grouped['user'][0];

        $this->assertEquals('Log out', $item['title']);
        $this->assertEquals('logout', $item['action']);
        $this->assertEquals('logout', $item['slug']);
        $this->assertFalse($item['active']);
    }

    public function test_children_sorted_by_order(): void
    {
        $this->navigation->add('Parent', '/parent', function (Section $section) {
            $section->attributes(['group' => 'main', 'order' => 0]);

            $section->add('Third', '/third', function (Section $child) {
                $child->attributes(['order' => 30]);
            });

            $section->add('First', '/first', function (Section $child) {
                $child->attributes(['order' => 10]);
            });

            $section->add('Second', '/second', function (Section $child) {
                $child->attributes(['order' => 20]);
            });
        });

        $grouped = $this->navigation->treeGrouped();
        $children = $grouped['main'][0]['children'];

        $this->assertEquals('First', $children[0]['title']);
        $this->assertEquals('Second', $children[1]['title']);
        $this->assertEquals('Third', $children[2]['title']);
    }
}
