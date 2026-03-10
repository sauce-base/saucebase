<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Header from '@/components/Header.vue';

import FaqSection from '@/pages/sections/FaqSection.vue';
import FeaturesSection from '@/pages/sections/FeaturesSection.vue';
import FilamentSection from '@/pages/sections/FilamentSection.vue';
import HeroSection from '@/pages/sections/HeroSection.vue';

import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>
            {{ $t('Saucebase - Modern Laravel SaaS Starter Kit') }}
        </title>
        <meta
            name="description"
            :content="$t('Your secret sauce for success')"
        />
    </Head>
    <div class="relative isolate flex min-h-screen flex-col overflow-x-hidden">
        <!-- Header with theme toggle -->
        <Header />

        <!-- Hero Section -->
        <HeroSection />

        <!-- Features Section -->
        <FeaturesSection />

        <!-- Filament Admin Section -->
        <FilamentSection />

        <!-- FAQ Section -->
        <FaqSection />

        <!-- Footer -->
        <Footer />
    </div>
</template>
