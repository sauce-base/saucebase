import { registerIcon } from '@/lib/navigation';
import IconSettings from '~icons/lucide/settings';
import IconUserCircle from '~icons/lucide/user-circle';

import '../css/style.css';

/**
 * Settings module setup
 * Called during app initialization before mounting
 *
 * NOTE: Navigation registration has been moved to backend SettingsServiceProvider.
 */
export function setup() {
    console.debug('Settings module loaded');

    registerIcon('settings', IconSettings);
    registerIcon('profile', IconUserCircle);
}

/**
 * Settings module after mount logic
 * Called after the app has been mounted
 */
export function afterMount(/* app: App */) {
    console.debug('Settings module after mount logic executed');
}
