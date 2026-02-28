// import type { App } from 'vue';

import '../css/style.css';

/**
 * Settings module setup
 * Called during app initialization before mounting
 *
 * NOTE: Navigation registration has been moved to backend SettingsServiceProvider.
 */
export function setup() {
    console.debug('Settings module loaded');
}

/**
 * Settings module after mount logic
 * Called after the app has been mounted
 */
export function afterMount(/* app: App */) {
    console.debug('Settings module after mount logic executed');
}
