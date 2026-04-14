<script setup lang="ts">
import Sonner from '@/components/ui/sonner/Sonner.vue';
import type { Toast } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue-sonner';

import 'vue-sonner/style.css';

const page = usePage();

function handleToastAction(actionType: string = 'route', onClick: string) {
    switch (actionType) {
        case 'route':
            router.visit(route(onClick));
            break;
        case 'url':
            router.visit(onClick);
            break;
        case 'external_url':
            window.location.href = onClick;
            break;
        default:
            console.warn(`Unknown toast action type: ${actionType}`);
    }
}

function showToast(data: Toast) {
    const position = data.position || 'bottom-right';
    const options: any = { position };

    if (data.description) {
        options.description = data.description;
    }

    if (data.action) {
        const action = data.action;
        options.action = {
            label: action.label,
            onClick: () => {
                if (action.onClick) {
                    handleToastAction(action.type || 'route', action.onClick);
                }
            },
        };
    }

    if (data.duration) {
        options.duration = data.duration;
    }

    switch (data.type) {
        case 'default':
            toast(data.message, options);
            break;
        case 'success':
            toast.success(data.message, options);
            break;
        case 'error':
            toast.error(data.message, options);
            break;
        case 'info':
            toast.info(data.message, options);
            break;
        case 'warning':
            toast.warning(data.message, options);
            break;
        case 'loading':
            toast.loading(data.message, options);
            break;
    }
}

router.on('flash', (event) => {
    if (event?.detail.flash.toast) {
        showToast(event.detail.flash.toast);
    }
});

watch(
    () => page.props?.toast,
    (toastData) => {
        if (toastData) {
            showToast(toastData);
        }
    },
    { immediate: true },
);
</script>
<template>
    <Sonner v-bind="$attrs" />
</template>
