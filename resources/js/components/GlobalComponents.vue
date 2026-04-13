<script setup lang="ts">
import { getGlobalComponents } from '@/lib/globalComponents';

const { position } = defineProps<{ position: 'top' | 'bottom' }>();
const components = getGlobalComponents(position);
</script>
<template>
    <component v-for="(C, i) in components" :key="i" :is="C" />
</template>
