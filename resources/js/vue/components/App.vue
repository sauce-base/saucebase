<script setup lang="ts">
import DynamicDialog from '@/components/DynamicDialog.vue';
import GlobalComponents from '@/components/GlobalComponents.vue';
import SettingsModal from '@/components/SettingsModal.vue';
import { Toaster } from '@/components/ui/sonner';
</script>
<template>
    <GlobalComponents position="top" />
    <Toaster />
    <slot />
    <DynamicDialog />
    <SettingsModal />
    <GlobalComponents position="bottom" />
</template>
