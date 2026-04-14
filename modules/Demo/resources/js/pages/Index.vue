<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Header from '@/components/Header.vue';

import FeaturesSection from './sections/FeaturesSection.vue';
import FilamentSection from './sections/FilamentSection.vue';

import { Head } from '@inertiajs/vue3';

import ProductSection from '@modules/Billing/resources/js/components/ProductSection.vue';
import type { Product } from '@modules/Billing/resources/js/types';
import HeroSection from './sections/HeroSection.vue';
import Testimonial from './sections/Testimonial.vue';

defineProps<{
    products?: Product[];
}>();
</script>

<template>
    <Head>
        <title>
            {{ $t('Saucebase | The best modular Laravel SaaS Starter Kit') }}
        </title>
        <meta
            name="description"
            :content="
                $t(
                    'Free, open-source Laravel SaaS starter kit. Ships with auth, billing, admin panel, and a modular copy-and-own architecture. Built on Laravel 13, Vue 3, Inertia.js, and Tailwind CSS 4.',
                )
            "
        />
    </Head>
    <div class="relative isolate flex min-h-screen flex-col overflow-x-hidden">
        <Header />
        <HeroSection />
        <FeaturesSection />
        <FilamentSection />
        <Testimonial />
        <ProductSection v-if="products?.length" :products="products" />
        <Footer />
    </div>
</template>
