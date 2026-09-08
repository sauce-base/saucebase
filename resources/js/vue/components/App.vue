<script setup lang="ts">
import DynamicDialog from '@/components/DynamicDialog.vue';
import GlobalComponents from '@/components/GlobalComponents.vue';
import { Toaster } from '@/components/ui/sonner';
import { useSettingsModalSync } from '@/composables/useSettingsModal';

useSettingsModalSync();
</script>
<template>
    <GlobalComponents position="top" />
    <Toaster />
    <slot />
    <DynamicDialog />
    <GlobalComponents position="bottom" />
</template>
