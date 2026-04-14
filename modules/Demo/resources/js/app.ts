// import { registerIcon } from '@/lib/navigation';
// import IconExample from '~icons/lucide/example';

import '../css/style.css';

/**
 * Demo module setup
 * Called during app initialization before mounting
 */
export function setup() {
    console.debug('Demo module loaded');

    // Register icons for navigation items defined in routes/navigation.php
    // registerIcon('demo', IconExample);
}

/**
 * Demo module after mount logic
 * Called after the app has been mounted
 */
export function afterMount() {
    console.debug('Demo module after mount logic executed');
}
