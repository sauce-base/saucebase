<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Header from '@/components/Header.vue';

import FeaturesSection from './sections/FeaturesSection.vue';
import FilamentSection from './sections/FilamentSection.vue';

import { Head } from '@inertiajs/vue3';

import ProductSection from '@modules/Billing/resources/js/components/ProductSection.vue';
import type { Product } from '@modules/Billing/resources/js/types';
import LatestPostsSection from '@modules/Blog/resources/js/components/LatestPostsSection.vue';
import HeroSection from './sections/HeroSection.vue';
import Testimonial from './sections/Testimonial.vue';

defineProps<{
    products?: Product[];
    latestPosts?: Modules.Blog.Data.PostData[];
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
        <ProductSection v-if="products?.length" :products="products">
            <div class="mx-auto max-w-4xl text-center">
                <h2
                    class="mt-2 text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl dark:text-white"
                >
                    {{ $t('Stripe integration') }}
                </h2>
                <p
                    class="mx-auto mt-6 max-w-2xl text-lg text-gray-600 dark:text-gray-400"
                >
                    {{
                        $t(
                            "This is a live example using Stripe Sandbox, so you won't be charged. Feel free to test this beautiful checkout flow",
                        )
                    }}
                </p>
            </div>
            <template #footer>
                <p
                    class="mt-10 text-center text-base text-gray-500 dark:text-gray-400"
                >
                    {{
                        $t(
                            'This is a live example, products and prices are pulled from your Stripe account and fully manageable from the',
                        )
                    }}
                    <a
                        :href="route('filament.admin.pages.dashboard')"
                        class="text-primary font-medium underline-offset-4 hover:underline"
                    >
                        {{ $t('admin panel') }}
                    </a>
                </p>

                <div class="absolute top-30 -z-10 h-2/3 w-full opacity-50">
                    <div
                        class="from-primary/20 to-secondary/20 dark:from-primary/30 dark:to-secondary/10 absolute inset-0 -top-50 -z-10 -skew-10 rounded-xl bg-radial"
                    />
                    <div
                        class="from-primary/20 to-secondary/20 dark:from-primary/30 dark:to-secondary/10 absolute inset-0 -top-30 -z-20 skew-30 rounded-xl bg-radial"
                    />
                </div>
            </template>
        </ProductSection>
        <LatestPostsSection v-if="latestPosts?.length" :posts="latestPosts" />
        <Footer />
    </div>
</template>
