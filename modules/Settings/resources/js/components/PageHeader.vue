<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

defineProps<{
    title: string;
    backUrl?: string;
}>();
</script>

<template>
    <h1 class="mt-2 text-2xl font-bold">
        <Link v-if="backUrl" :href="backUrl">
            <ArrowLeft class="mr-2 mb-1 inline size-6" />
        </Link>
        {{ title }}
    </h1>
</template>
