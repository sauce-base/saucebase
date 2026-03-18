import { useDialog } from '@/composables/useDialog';
import { registerAction, registerIcon } from '@/lib/navigation';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { LogOut } from 'lucide-vue-next';
import IconLogOut from '~icons/lucide/log-out';

import '../css/style.css';

/**
 * Auth module setup
 * Called during app initialization before mounting
 */
export function setup() {
    console.debug('Auth module loaded');

    registerIcon('logout', IconLogOut);
    registerAuthActions();
}

/**
 * Register auth-related navigation actions
 */
function registerAuthActions() {
    // Logout action
    registerAction('logout', async (event: MouseEvent) => {
        event.preventDefault();

        const { confirm } = useDialog();
        if (
            await confirm({
                title: trans('Log out'),
                description: trans(
                    'Are you sure you want to log out? You will need to sign in again.',
                ),
                confirmLabel: trans('Log out'),
                cancelLabel: trans('Cancel'),
                variant: 'destructive',
                icon: LogOut,
                align: 'left',
            })
        ) {
            router.post(route('logout'));
        }
    });
}

/**
 * Auth module after mount logic
 * Called after the app has been mounted
 */
export function afterMount() {
    console.debug('Auth module after mount logic executed');
}
