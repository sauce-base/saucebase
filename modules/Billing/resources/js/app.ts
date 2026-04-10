import { registerIcon } from '@/lib/navigation';
import IconCreditCard from '~icons/lucide/credit-card';
import IconSparkles from '~icons/lucide/sparkles';

import '../css/style.css';

/**
 * Billing module setup
 * Called during app initialization before mounting
 */
export function setup() {
    console.debug('Billing module loaded');

    registerIcon('billing', IconCreditCard);
    registerIcon('upgrade', IconSparkles);
}

/**
 * Billing module after mount logic
 * Called after the app has been mounted
 */
export function afterMount() {
    console.debug('Billing module after mount logic executed');
}
