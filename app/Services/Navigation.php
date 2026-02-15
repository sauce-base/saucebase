<?php

namespace App\Services;

use Illuminate\Support\Str;
use Spatie\Navigation\Navigation as SpatieNavigation;
use App\Navigation\Section;

/**
 * Custom Navigation Service.
 *
 * Extends Spatie's Navigation to add group-based filtering and transformation
 * to MenuItem format for frontend consumption.
 */
class Navigation extends SpatieNavigation
{
    /**
     * Add a navigation item.
     *
     * Overrides Spatie's add() to create App\Navigation\Section instances
     * instead of Spatie\Navigation\Section, keeping imports consistent.
     */
    public function add(string $title = '', string $url = '', ?callable $configure = null): self
    {
        $section = new Section($this, $title, $url);

        if ($configure) {
            $configure($section);
        }

        $this->children[] = $section;

        return $this;
    }

    /**
     * Add a navigation item with a runtime condition check.
     *
     * Unlike addIf (which checks at registration time), addWhen stores a callable
     * that is evaluated at render time, allowing for dynamic visibility based on
     * runtime state (e.g., current user permissions, feature flags).
     *
     * @param  callable  $condition  Callable that returns bool, evaluated at render time
     * @param  string  $title  Navigation item title
     * @param  string  $url  Navigation item URL
     * @param  callable|null  $configure  Optional callback to configure the Section
     */
    public function addWhen(callable $condition, string $title = '', string $url = '', ?callable $configure = null): self
    {
        $this->add($title, $url, function (Section $section) use ($condition, $configure) {
            // Store the 'when' callable for runtime evaluation
            $section->attributes([
                'when' => $condition,
            ]);

            // Apply additional configuration if provided
            if ($configure) {
                $configure($section);
            }
        });

        return $this;
    }

    /**
     * Get all navigation items grouped by their 'group' attribute.
     *
     * Returns an associative array where keys are group names and values are
     * arrays of MenuItem objects. Items are filtered by 'when' callables and
     * transformed to the MenuItem format.
     *
     * This is more efficient than calling treeByGroup() multiple times, as it
     * processes the entire navigation tree once and groups items automatically.
     *
     * @return array Associative array of grouped MenuItems, e.g.:
     *               [
     *               'main' => [...MenuItem[]],
     *               'settings' => [...MenuItem[]],
     *               'user' => [...MenuItem[]],
     *               ]
     */
    public function treeGrouped(): array
    {
        $tree = $this->tree();

        // Group items by their 'group' attribute
        $grouped = [];
        foreach ($tree as $item) {
            $group = $item['attributes']['group'] ?? 'ungrouped';
            if (! isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][] = $item;
        }

        // Filter and transform each group to MenuItem format
        foreach ($grouped as $group => $items) {
            $filtered = $this->filterCallable($items);
            $grouped[$group] = $this->transformTree($filtered);
        }

        return $grouped;
    }

    /**
     * Filter navigation items based on 'when' callable attribute.
     *
     * Items without a 'when' attribute are included by default.
     * Items with a 'when' callable are included only if the callable returns true.
     *
     * @param  array  $tree  Spatie navigation tree
     * @return array Filtered navigation items
     */
    protected function filterCallable(array $tree): array
    {
        $filtered = array_filter($tree, function ($item) {
            $callable = $item['attributes']['when'] ?? null;

            // If no 'when' callable exists, include the item by default
            if ($callable === null) {
                return true;
            }

            // If 'when' exists, include only if it's callable and returns true
            return is_callable($callable) && $callable();
        });

        // Recursively filter children
        $filtered = array_map(function ($item) {
            if (! empty($item['children'])) {
                $item['children'] = $this->filterCallable($item['children']);
            }

            return $item;
        }, $filtered);

        return array_values($filtered);
    }

    /**
     * Transform Spatie navigation tree to MenuItem array.
     *
     * @param  array  $tree  Spatie navigation tree
     * @return array MenuItem array
     */
    protected function transformTree(array $tree): array
    {
        // Sort by order attribute before transforming
        $tree = $this->sortByOrder($tree);

        return array_map(
            fn (array $item) => $this->transformItem($item),
            $tree
        );
    }

    /**
     * Transform a single navigation item to MenuItem format.
     *
     * Converts Spatie's navigation item structure to a clean MenuItem object,
     * filtering out internal attributes (when, group, order) and calculating
     * the active state based on route matching.
     *
     * @param  array  $item  Spatie navigation item
     * @return array MenuItem with structure:
     *               [
     *               'title' => string,
     *               'url' => string|null,
     *               'active' => bool,
     *               'icon' => string|null,      // optional
     *               'action' => string|null,    // optional
     *               'type' => string|null,      // optional
     *               'external' => bool|null,    // optional
     *               'children' => array|null,   // optional
     *               ]
     */
    protected function transformItem(array $item): array
    {
        $attributes = $item['attributes'] ?? [];

        // Build base MenuItem structure with required fields
        $menuItem = [
            'title' => $item['title'],
            'active' => $this->isItemActive($item),
            'slug' => $attributes['slug'] ?? Str::slug($item['title'], '-'),
        ];

        // Add URL from the item (stored at root level by Spatie)
        if (isset($item['url'])) {
            $menuItem['url'] = $item['url'];
        }

        // Add optional attributes if they exist (internal attributes like 'when', 'group', 'order' are excluded)
        $optionalFields = ['action', 'type', 'external', 'newPage', 'class', 'badge'];
        foreach ($optionalFields as $field) {
            if (isset($attributes[$field])) {
                $menuItem[$field] = $attributes[$field];
            }
        }

        // Transform children recursively
        if (! empty($item['children'])) {
            $menuItem['children'] = $this->transformTree($item['children']);
        }

        return $menuItem;
    }

    /**
     * Check if a navigation item is active based on current URL.
     *
     * Uses exact matching only. Parent items should handle their own active state
     * by checking if any of their children are active.
     *
     * @param  array  $item  Spatie navigation item
     * @return bool Whether the item is active
     */
    protected function isItemActive(array $item): bool
    {
        $itemUrl = $item['url'] ?? null;

        if (! $itemUrl) {
            return false;
        }

        // Get current request URL
        $currentUrl = request()->url();

        // Normalize URLs by removing trailing slashes
        $itemPath = rtrim(parse_url($itemUrl, PHP_URL_PATH), '/');
        $currentPath = rtrim(parse_url($currentUrl, PHP_URL_PATH), '/');

        // Only exact match
        return $currentPath === $itemPath;
    }

    /**
     * Sort navigation items by order attribute.
     *
     * @param  array  $items  Navigation items
     * @return array Sorted items
     */
    protected function sortByOrder(array $items): array
    {
        usort($items, function ($a, $b) {
            $orderA = $a['attributes']['order'] ?? 999;
            $orderB = $b['attributes']['order'] ?? 999;

            return $orderA <=> $orderB;
        });

        return $items;
    }

    /**
     * Load the navigation items from navigation.php files.
     *
     * Looks for navigation.php files in:
     * - routes/navigation.php (core navigation)
     * - modules/{ModuleName}/routes/navigation.php (module navigation)
     *
     * Only loads module navigation if the module is enabled.
     *
     * @return $this
     */
    public function load(): self
    {
        // Load core navigation
        $coreNavigationPath = base_path('routes/navigation.php');
        if (file_exists($coreNavigationPath)) {
            require_once $coreNavigationPath;
        }

        // Load module navigation
        $modulesStatusPath = base_path('modules_statuses.json');
        if (file_exists($modulesStatusPath)) {
            $modulesStatus = json_decode(file_get_contents($modulesStatusPath), true);

            foreach ($modulesStatus as $moduleName => $enabled) {
                if ($enabled) {
                    $moduleNavigationPath = base_path("modules/{$moduleName}/routes/navigation.php");
                    if (file_exists($moduleNavigationPath)) {
                        require_once $moduleNavigationPath;
                    }
                }
            }
        }

        return $this;
    }
}
