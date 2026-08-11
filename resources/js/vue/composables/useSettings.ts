import { usePage } from '@inertiajs/vue3';
import type { Settings } from '@js/settings';
import { computed, type ComputedRef } from 'vue';

export function useSettings(): ComputedRef<Settings> {
    const page = usePage();

    return computed(() => page.props.settings);
}
