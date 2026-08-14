<script setup lang="ts">
import {
    getGlobalComponents,
    type GlobalComponentSlot,
} from '@/lib/globalComponents';

const { position } = defineProps<{ position: GlobalComponentSlot }>();
const components = getGlobalComponents(position);
</script>
<template>
    <component v-for="(C, i) in components" :key="i" :is="C" />
</template>
