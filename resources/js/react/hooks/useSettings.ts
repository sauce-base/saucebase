import { usePage } from '@inertiajs/react';
import type { Settings } from '@js/settings';

export function useSettings(): Settings {
    return usePage().props.settings;
}
