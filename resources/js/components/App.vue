<script setup lang="ts">
import GlobalComponents from '@/components/GlobalComponents.vue';
import DynamicDialog from '@/components/DynamicDialog.vue';
import { Toaster } from '@/components/ui/sonner';
</script>
<template>
    <GlobalComponents position="top" />
    <slot />
    <Toaster />
    <DynamicDialog />
    <GlobalComponents position="bottom" />
</template>
