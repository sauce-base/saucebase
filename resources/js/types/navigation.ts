/**
 * Navigation type definitions.
 *
 * Simplified navigation structure using convention-based config files.
 */

/**
 * Navigation prop structure (shared via Inertia).
 */
export interface Navigation {
    main: MenuItem[];
    secondary: MenuItem[];
    settings: MenuItem[];
    user: MenuItem[];
    landing: MenuItem[];
    // Additional sections can be added as needed
}

/**
 * Badge configuration for navigation items.
 */
export interface MenuBadge {
    content?: string | number; // Badge text/number (if omitted, shows small dot)
    variant?: 'default' | 'secondary' | 'destructive' | 'outline';
    class?: string; // Additional custom classes
}

/**
 * Menu item.
 */
export interface MenuItem {
    id?: string;
    title: string;
    route?: string; // Laravel route name (for active state matching)
    url?: string; // Generated URL (for navigation)
    slug?: string; // Navigation slug (e.g., 'settings', 'dashboard')
    permission?: string; // Server-side filtered
    action?: string; // For action buttons
    type?: 'label' | 'separator';
    active?: boolean; // Server-side active state from Spatie
    external?: boolean; // If true, use regular anchor tag instead of Inertia Link
    newPage?: boolean; // If true, open link in new tab (target="_blank")
    badge?: MenuBadge | boolean; // Badge configuration or true for simple dot
    children?: MenuItem[];
    class?: string; // Additional CSS classes
}
