import { registerIcon } from '@/lib/navigation';
import IconMap from '~icons/heroicons/map';

import '../css/style.css';

/**
 * Roadmap module setup
 * Called during app initialization before mounting
 */
export function setup() {
    console.debug('Roadmap module loaded');

    registerIcon('roadmap', IconMap);
}

/**
 * Roadmap module after mount logic
 * Called after the app has been mounted
 */
export function afterMount() {
    console.debug('Roadmap module after mount logic executed');
}
