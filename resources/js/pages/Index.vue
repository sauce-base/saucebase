<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import Footer from '@/components/Footer.vue';
import Header from '@/components/Header.vue';
import { modules } from '@/composables/useModules';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

import type { Product } from '@modules/Billing/resources/js/types';

import ProductSection from '@modules/Billing/resources/js/components/ProductSection.vue';

import IconGitHub from '~icons/heroicons/code-bracket';
import IconAI from '~icons/heroicons/sparkles';
import IconDashboard from '~icons/heroicons/squares-2x2';
import IconUserPlus from '~icons/heroicons/user-plus';

defineProps<{
    products?: Product[];
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user);
const title = 'Sauce Base - Modern Laravel SaaS Starter Kit';

// Mouse tracking for parallax effect
const mouseX = ref(0);
const mouseY = ref(0);

const isServer = typeof window === 'undefined';

const handleMouseMove = (e: MouseEvent) => {
    if (isServer) return;
    mouseX.value = (e.clientX / window.innerWidth - 0.5) * 60;
    mouseY.value = (e.clientY / window.innerHeight - 0.5) * 60;
};

onMounted(() => {
    window.addEventListener('mousemove', handleMouseMove);
});

onUnmounted(() => {
    window.removeEventListener('mousemove', handleMouseMove);
});
</script>

<template>
    <Head>
        <title>
            {{ $t(title) }}
        </title>
        <meta
            name="description"
            :content="$t('Your secret sauce for success')"
        />
    </Head>
    <div class="relative isolate flex min-h-screen flex-col overflow-x-hidden">
        <!-- Header with theme toggle -->
        <Header />

        <!-- Top gradient blob -->
        <div
            class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80"
            aria-hidden="true"
        >
            <div
                class="from-secondary to-primary relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr opacity-30 transition-transform duration-300 ease-out sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"
                :style="`clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%); transform: translate(${mouseX}px, ${mouseY}px)`"
            ></div>
        </div>

        <!-- Bottom gradient blob -->
        <div
            class="pointer-events-none absolute inset-x-0 right-0 -z-10 transform-gpu overflow-hidden blur-3xl"
            aria-hidden="true"
        >
            <div
                class="from-secondary to-primary relative left-[calc(50%-10rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 translate-y-1/4 bg-gradient-to-tr opacity-30 transition-transform duration-300 ease-out sm:left-[calc(50%+10rem)] sm:w-[72.1875rem]"
                :style="`clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%); transform: translate(${-mouseX}px, ${-mouseY}px)`"
            ></div>
        </div>

        <!-- Main Content -->
        <main
            class="relative flex flex-1 flex-col items-center justify-center bg-white/50 px-6 py-24 sm:py-32 dark:bg-gray-900/50"
        >
            <div class="mx-auto max-w-4xl text-center">
                <!-- Logo -->
                <div class="mb-12 flex justify-center">
                    <AppLogo showText size="xxl" showSubtitle centered />
                </div>

                <div class="hidden sm:mb-8 sm:flex sm:justify-center">
                    <div
                        class="relative flex items-center gap-x-2 rounded-full px-3 py-1 text-sm/6 text-gray-600 ring-1 ring-gray-900/10 hover:ring-gray-900/20 dark:text-gray-400 dark:ring-white/10 dark:hover:ring-white/20"
                    >
                        <IconAI
                            class="size-4 text-indigo-500 dark:text-indigo-400"
                        />
                        {{ $t('Optimized for AI-assisted development') }}
                        <a
                            href="https://sauce-base.github.io/docs/"
                            class="text-secondary font-semibold"
                        >
                            <span aria-hidden="true" class="absolute inset-0" />
                            {{ $t('Read more') }}
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Main Headline -->
                <h1 class="mb-6 text-4xl font-bold tracking-tight sm:text-6xl">
                    {{ $t('Modern Laravel SaaS Starter Kit') }}
                </h1>

                <!-- Subheadline -->
                <p class="mb-8 text-xl text-gray-600 dark:text-gray-400">
                    {{
                        $t(
                            'Clone the repo, start building scalable and maintainable SaaS applications quickly. Built with the VILT stack - completely free and open source.',
                        )
                    }}
                </p>

                <!-- Action Buttons -->
                <div
                    class="flex flex-col items-center justify-center gap-4 sm:flex-row"
                >
                    <!-- Primary CTA -->
                    <Link
                        v-if="modules().has('auth') && !user"
                        :href="route('register')"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary inline-flex items-center justify-center rounded-full px-8 py-4 text-lg font-semibold transition-all duration-200 hover:scale-105 focus:ring-2 focus:ring-offset-2 focus:outline-hidden dark:focus:ring-offset-gray-950"
                    >
                        <IconUserPlus class="mr-2 h-5 w-5" />
                        {{ $t('Get Started Free') }}
                    </Link>

                    <!-- Dashboard Button (if logged in) -->
                    <Link
                        v-if="route().has('dashboard') && user"
                        :href="route('dashboard')"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary inline-flex items-center justify-center rounded-full px-8 py-4 text-lg font-semibold transition-all duration-200 hover:scale-105 focus:ring-2 focus:ring-offset-2 focus:outline-hidden dark:focus:ring-offset-gray-950"
                    >
                        <IconDashboard class="mr-2 h-5 w-5" />
                        {{ $t('Go to Dashboard') }}
                    </Link>

                    <!-- GitHub Button -->
                    <a
                        href="https://github.com/sauce-base/saucebase"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="focus:ring-primary inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-8 py-4 text-lg font-semibold text-gray-700 transition-all duration-200 hover:scale-105 hover:bg-gray-50 focus:ring-2 focus:ring-offset-2 focus:outline-hidden dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-950"
                    >
                        <IconGitHub class="mr-2 h-5 w-5" />
                        {{ $t('View on GitHub') }}
                    </a>

                    <!-- Sign In Button -->
                    <Link
                        v-if="modules().has('auth') && !user"
                        :href="route('login')"
                        class="text-primary hover:text-primary/90 dark:text-white dark:hover:text-white/80"
                    >
                        {{ $t('Already have an account? Sign In') }}
                    </Link>
                </div>
            </div>
        </main>

        <!-- Features Section -->
        <div
            id="features"
            class="relative isolate overflow-hidden border-t border-gray-200 bg-white/50 py-24 sm:py-32 dark:border-gray-800 dark:bg-gray-900/90"
        >
            <div class="absolute inset-0 -z-10 overflow-hidden">
                <svg
                    aria-hidden="true"
                    class="absolute top-0 left-[max(50%,25rem)] h-256 w-512 -translate-x-1/2 mask-[radial-gradient(64rem_64rem_at_top,white,transparent)] stroke-gray-300/70 dark:stroke-gray-700/50"
                >
                    <defs>
                        <pattern
                            id="e813992c-7d03-4cc4-a2bd-151760b470a0"
                            width="200"
                            height="200"
                            x="50%"
                            y="-1"
                            patternUnits="userSpaceOnUse"
                        >
                            <path d="M100 200V.5M.5 .5H200" fill="none" />
                        </pattern>
                    </defs>
                    <svg
                        x="50%"
                        y="-1"
                        class="overflow-visible fill-gray-200 dark:fill-gray-800/50"
                    >
                        <path
                            d="M-100.5 0h201v201h-201Z M699.5 0h201v201h-201Z M499.5 400h201v201h-201Z M-300.5 600h201v201h-201Z"
                            stroke-width="0"
                        />
                    </svg>
                    <rect
                        width="100%"
                        height="100%"
                        fill="url(#e813992c-7d03-4cc4-a2bd-151760b470a0)"
                        stroke-width="0"
                    />
                </svg>
            </div>
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div
                    class="mx-auto grid max-w-2xl grid-cols-1 gap-x-8 gap-y-16 sm:gap-y-20 lg:mx-0 lg:max-w-none lg:grid-cols-2"
                >
                    <div class="lg:pt-4 lg:pr-8">
                        <div class="lg:max-w-lg">
                            <h2
                                class="text-2xl font-semibold text-indigo-600 dark:text-indigo-400"
                            >
                                {{ $t('Built for developers') }}
                            </h2>
                            <p
                                class="mt-2 text-4xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-5xl dark:text-white"
                            >
                                {{ $t('Everything you need to ship fast') }}
                            </p>
                            <p
                                class="mt-6 text-lg/8 text-gray-700 dark:text-gray-300"
                            >
                                {{
                                    $t(
                                        'Saucebase comes with authentication, billing, admin panel, and a modular architecture out of the box. Focus on building features, not boilerplate.',
                                    )
                                }}
                            </p>
                            <div
                                class="mt-10 max-w-xl space-y-8 text-base/7 text-gray-600 lg:max-w-none dark:text-gray-400"
                            >
                                <div class="relative pl-9">
                                    <svg
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                        aria-hidden="true"
                                        class="absolute top-1 left-1 size-5 text-indigo-600 dark:text-indigo-400"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M4.25 2A2.25 2.25 0 0 0 2 4.25v11.5A2.25 2.25 0 0 0 4.25 18h11.5A2.25 2.25 0 0 0 18 15.75V4.25A2.25 2.25 0 0 0 15.75 2H4.25Zm4.03 6.28a.75.75 0 0 0-1.06-1.06L4.97 9.47a.75.75 0 0 0 0 1.06l2.25 2.25a.75.75 0 0 0 1.06-1.06L6.56 10l1.72-1.72Zm2.38-1.06a.75.75 0 1 1 1.06 1.06L10.06 10l1.72 1.72a.75.75 0 1 1-1.06 1.06l-2.25-2.25a.75.75 0 0 1 0-1.06l2.25-2.25Z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <h3
                                        class="font-semibold text-gray-900 dark:text-white"
                                    >
                                        {{ $t('Modular architecture') }}
                                    </h3>
                                    <p class="mt-2">
                                        {{
                                            $t(
                                                'Install only what you need. Each module is self-contained with its own routes, views, and migrations. Copy-and-own means full customization.',
                                            )
                                        }}
                                    </p>
                                </div>
                                <div class="relative pl-9">
                                    <svg
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                        aria-hidden="true"
                                        class="absolute top-1 left-1 size-5 text-indigo-600 dark:text-indigo-400"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M14.5 10a4.5 4.5 0 0 0 4.284-5.882c-.105-.324-.51-.391-.752-.15L15.34 6.66a.454.454 0 0 1-.493.11 3.01 3.01 0 0 1-1.618-1.616.455.455 0 0 1 .11-.494l2.694-2.692c.24-.241.174-.647-.15-.752a4.5 4.5 0 0 0-5.873 4.575c.055.873-.128 1.808-.8 2.368l-7.23 6.024a2.724 2.724 0 1 0 3.837 3.837l6.024-7.23c.56-.672 1.495-.855 2.368-.8.096.007.193.01.291.01ZM5 16a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <h3
                                        class="font-semibold text-gray-900 dark:text-white"
                                    >
                                        {{ $t('Modern tooling') }}
                                    </h3>
                                    <p class="mt-2">
                                        {{
                                            $t(
                                                'TypeScript, Vite with HMR, PHPStan level 9, ESLint, and Prettier configured out of the box. Write clean, type-safe code from day one.',
                                            )
                                        }}
                                    </p>
                                </div>
                                <div class="relative pl-9">
                                    <svg
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                        aria-hidden="true"
                                        class="absolute top-1 left-1 size-5 text-indigo-600 dark:text-indigo-400"
                                    >
                                        <path
                                            d="M4.632 3.533A2 2 0 0 1 6.577 2h6.846a2 2 0 0 1 1.945 1.533l1.976 8.234A3.489 3.489 0 0 0 16 11.5H4c-.476 0-.93.095-1.344.267l1.976-8.234Z"
                                        />
                                        <path
                                            fill-rule="evenodd"
                                            d="M4 13a2 2 0 1 0 0 4h12a2 2 0 1 0 0-4H4Zm11.24 2a.75.75 0 0 1 .75-.75H16a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75h-.01a.75.75 0 0 1-.75-.75V15Zm-2.25-.75a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75H13a.75.75 0 0 0 .75-.75V15a.75.75 0 0 0-.75-.75h-.01Z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <h3
                                        class="font-semibold text-gray-900 dark:text-white"
                                    >
                                        {{ $t('Docker-first setup') }}
                                    </h3>
                                    <p class="mt-2">
                                        {{
                                            $t(
                                                'Nginx, MySQL, Redis, and Mailpit pre-configured with SSL. Run one command and start developing with hot reload enabled.',
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <img
                        src="/images/screenshots/dashboard-dark.png"
                        :alt="$t('Saucebase dashboard screenshot')"
                        class="w-3xl max-w-none rounded-xl p-3 shadow-xl ring-1 ring-gray-400/10 not-dark:hidden sm:w-228 md:-ml-4 lg:ml-0 dark:ring-white/30"
                    />
                    <img
                        src="/images/screenshots/dashboard-light.png"
                        :alt="$t('Saucebase dashboard screenshot')"
                        class="w-3xl max-w-none rounded-xl p-3 shadow-2xl ring-1 shadow-black ring-gray-400/30 sm:w-228 md:-ml-4 lg:ml-0 dark:hidden dark:ring-white/30"
                    />
                </div>
            </div>
        </div>

        <!-- Pricing Section -->
        <ProductSection v-if="products" :products="products" />

        <!-- Footer -->
        <Footer />
    </div>
</template>
