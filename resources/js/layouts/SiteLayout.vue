<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Header from '@/components/Header.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    title?: string;
    description?: string;
    image?: string;
    canonical?: string;
    type?: 'website' | 'article';
}>();

const page = usePage();
const appName = computed(() => (page.props as Record<string, unknown>).appName as string ?? 'Saucebase');
const ogType = computed(() => props.type ?? 'website');
</script>

<template>
    <Head :title="title">
        <!-- Basic -->
        <meta v-if="description" name="description" :content="description" />
        <link v-if="canonical" rel="canonical" :href="canonical" />

        <!-- Open Graph -->
        <meta property="og:type" :content="ogType" />
        <meta v-if="title" property="og:title" :content="title" />
        <meta v-if="description" property="og:description" :content="description" />
        <meta v-if="image" property="og:image" :content="image" />
        <meta v-if="canonical" property="og:url" :content="canonical" />
        <meta property="og:site_name" :content="appName" />

        <!-- Twitter Card -->
        <meta name="twitter:card" :content="image ? 'summary_large_image' : 'summary'" />
        <meta v-if="title" name="twitter:title" :content="title" />
        <meta v-if="description" name="twitter:description" :content="description" />
        <meta v-if="image" name="twitter:image" :content="image" />
    </Head>
    <div class="bg-background relative isolate flex min-h-screen flex-col">
        <Header />
        <slot />
        <Footer />
    </div>
</template>
