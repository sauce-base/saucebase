<script setup lang="ts">
import { resolveIcon } from '@/lib/navigation';
import { computed, type Component } from 'vue';
import IconHelpCircle from '~icons/lucide/help-circle';

const props = defineProps<{ icon?: string | null }>();

const iconComponent = computed<Component | null>(() => {
    if (!props.icon) return null;

    const icon = resolveIcon(props.icon) ?? null;

    if (!icon && import.meta.env.DEV) {
        console.warn(`[NavIcon] No icon registered for: "${props.icon}"`);
        return IconHelpCircle;
    }

    return icon;
});
</script>

<template>
    <component :is="iconComponent" v-if="iconComponent" />
</template>
