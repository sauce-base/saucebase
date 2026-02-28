<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import Footer from '@/components/Footer.vue';
import PageTransition from '@/components/PageTransition.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
}>();
</script>

<template>
    <div class="flex min-h-screen flex-col bg-gray-50 dark:bg-gray-950">
        <Head :title="title" />

        <!-- Header -->
        <header
            class="border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="mx-auto max-w-5xl">
                <Link :href="route('index')">
                    <AppLogo size="sm" :showText="true" />
                </Link>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 px-6 py-10">
            <div class="mx-auto max-w-5xl">
                <PageTransition>
                    <slot />
                </PageTransition>
            </div>
        </main>

        <!-- Footer -->
        <Footer />
    </div>
</template>
