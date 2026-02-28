import { registerActionHandler } from '@/utils/actionHandlers';
import { router } from '@inertiajs/vue3';

import '../css/style.css';

/**
 * Auth module setup
 * Called during app initialization before mounting
 */
export function setup() {
    console.debug('Auth module loaded');

    registerAuthActions();
}

/**
 * Register auth-related navigation actions
 */
function registerAuthActions() {
    // Logout action
    registerActionHandler('logout', async (event: MouseEvent) => {
        event.preventDefault();

        // TODO: Use i18n for confirmation message
        if (!confirm('Are you sure you want to log out?')) {
            return;
        }

        router.post(route('logout'));
    });
}

/**
 * Auth module after mount logic
 * Called after the app has been mounted
 */
export function afterMount() {
    console.debug('Auth module after mount logic executed');
}
