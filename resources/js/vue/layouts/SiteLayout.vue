<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Header from '@/components/Header.vue';
import { useSettings } from '@/composables/useSettings';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    title?: string;
    description?: string;
    image?: string;
    canonical?: string;
    type?: 'website' | 'article';
}>();

const settings = useSettings();
const pageDescription = computed(
    () => props.description ?? settings.value.general.site_description,
);
const ogType = computed(() => props.type ?? 'website');
</script>

<template>
    <Head :title="title">
        <!-- Basic -->
        <meta
            v-if="pageDescription"
            head-key="description"
            data-testid="app-description"
            name="description"
            :content="pageDescription"
        />
        <link v-if="canonical" rel="canonical" :href="canonical" />

        <!-- Open Graph -->
        <meta property="og:type" :content="ogType" />
        <meta v-if="title" property="og:title" :content="title" />
        <meta
            v-if="pageDescription"
            property="og:description"
            :content="pageDescription"
        />
        <meta v-if="image" property="og:image" :content="image" />
        <meta v-if="canonical" property="og:url" :content="canonical" />
        <meta property="og:site_name" :content="settings.general.site_name" />

        <!-- Twitter Card -->
        <meta
            name="twitter:card"
            :content="image ? 'summary_large_image' : 'summary'"
        />
        <meta v-if="title" name="twitter:title" :content="title" />
        <meta
            v-if="pageDescription"
            name="twitter:description"
            :content="pageDescription"
        />
        <meta v-if="image" name="twitter:image" :content="image" />
    </Head>
    <div class="bg-background relative isolate flex min-h-screen flex-col">
        <Header />
        <slot />
        <Footer />
    </div>
</template>
