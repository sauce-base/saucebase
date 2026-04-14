<script setup lang="ts">
import DynamicDialog from '@/components/DynamicDialog.vue';
import GlobalComponents from '@/components/GlobalComponents.vue';
import { Toaster } from '@/components/ui/sonner';
</script>
<template>
    <GlobalComponents position="top" />
    <slot />
    <Toaster />
    <DynamicDialog />
    <GlobalComponents position="bottom" />
</template>
