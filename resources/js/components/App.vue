<script setup lang="ts">
import DynamicDialog from '@/components/DynamicDialog.vue';
import GlobalComponents from '@/components/GlobalComponents.vue';
import { Toaster } from '@/components/ui/sonner';
</script>
<template>
    <GlobalComponents position="top" />
    <Toaster />
    <slot />
    <DynamicDialog />
    <GlobalComponents position="bottom" />
</template>
