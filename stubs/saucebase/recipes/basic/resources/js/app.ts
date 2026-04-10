// import { registerIcon } from '@/lib/navigation';
// import IconExample from '~icons/lucide/example';

import '../css/style.css';

/**
 * {Module} module setup
 * Called during app initialization before mounting
 */
export function setup() {
    console.debug('{Module} module loaded');

    // Register icons for navigation items defined in routes/navigation.php
    // registerIcon('{module-}', IconExample);
}

/**
 * {Module} module after mount logic
 * Called after the app has been mounted
 */
export function afterMount() {
    console.debug('{Module} module after mount logic executed');
}
