import { usePage } from '@inertiajs/vue3';

/**
 * Composable for checking module availability
 *
 * Provides methods to check if modules are enabled in the application.
 * Module state is shared from the backend via Inertia props.
 *
 * @example
 * const { has, all } = useModules();
 * if (has('Auth')) { ... }
 *
 * // Or use the convenience alias:
 * if (modules().has('Billing')) { ... }
 */
export function useModules() {
    const page = usePage();

    return {
        /**
         * Check if a module is enabled (by key or name)
         */
        has(name: string): boolean {
            const modules = page.props.modules ?? {};
            const lower = name.toLowerCase();
            return Object.keys(modules).some((k) => k.toLowerCase() === lower)
                || Object.values(modules).some((v) => (v as string).toLowerCase() === lower);
        },

        /**
         * Get all enabled module names
         */
        all(): string[] {
            return Object.values(page.props.modules ?? {});
        },
    };
}

// Convenience alias: modules().has('Auth')
export const modules = useModules;
