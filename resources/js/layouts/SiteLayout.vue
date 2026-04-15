<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Header from '@/components/Header.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <Head :title="title">
        <meta v-if="description" name="description" :content="description" />
    </Head>
    <div class="bg-background relative isolate flex min-h-screen flex-col">
        <Header />
        <slot />
        <Footer />
    </div>
</template>
