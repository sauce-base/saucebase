import type { Component } from 'vue';
import { ref } from 'vue';

export interface ConfirmOptions {
    title: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'default' | 'destructive';
    icon?: Component;
    align?: 'center' | 'left';
}

const isOpen = ref(false);
const options = ref<ConfirmOptions>({ title: '' });
let resolveCallback: ((value: boolean) => void) | null = null;

export function useDialog() {
    function confirm(opts: ConfirmOptions): Promise<boolean> {
        options.value = opts;
        isOpen.value = true;
        return new Promise<boolean>((resolve) => {
            resolveCallback = resolve;
        });
    }

    function resolve(confirmed: boolean) {
        isOpen.value = false;
        resolveCallback?.(confirmed);
        resolveCallback = null;
    }

    return { confirm, isOpen, options, resolve };
}
