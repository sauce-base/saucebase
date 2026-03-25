<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import IconGitHub from '~icons/heroicons/code-bracket';
import IconAI from '~icons/heroicons/sparkles';
import IconDashboard from '~icons/heroicons/squares-2x2';
import IconUserPlus from '~icons/heroicons/user-plus';

import { modules } from '@/composables/useModules';
import { usePage } from '@inertiajs/vue3';
import { computed, inject, onMounted, onUnmounted, ref } from 'vue';

import { route as ziggyRoute } from 'ziggy-js';
const route = inject<typeof ziggyRoute>('route')!;

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const hasAuthModule = computed(() => modules().has('auth'));
const hasDashboardRoute = computed(() => route().has('dashboard'));

// Mouse tracking for parallax effect
const mouseX = ref(0);
const mouseY = ref(0);

const handleMouseMove = (e: MouseEvent) => {
    mouseX.value = (e.clientX / window?.innerWidth - 0.5) * 60;
    mouseY.value = (e.clientY / window?.innerHeight - 0.5) * 60;
};

onMounted(() => {
    window.addEventListener('mousemove', handleMouseMove);
});

onUnmounted(() => {
    window.removeEventListener('mousemove', handleMouseMove);
});
</script>

<template>
    <main
        class="relative flex flex-1 flex-col items-center justify-center bg-white/50 px-6 py-24 sm:py-32 dark:bg-gray-900/50"
    >
        <!-- Top gradient blob -->
        <div
            class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80"
            aria-hidden="true"
        >
            <div
                class="from-secondary to-primary relative left-[calc(50%-11rem)] aspect-1155/678 w-144.5 -translate-x-1/2 rotate-30 bg-linear-to-tr opacity-30 transition-transform duration-300 ease-out sm:left-[calc(50%-30rem)] sm:w-288.75"
                :style="`clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%); transform: translate(${mouseX}px, ${mouseY}px)`"
            ></div>
        </div>

        <!-- Bottom gradient blob -->
        <div
            class="pointer-events-none absolute inset-x-0 right-0 -z-10 transform-gpu overflow-hidden blur-3xl"
            aria-hidden="true"
        >
            <div
                class="from-secondary to-primary relative left-[calc(50%-10rem)] aspect-1155/678 w-144.5 -translate-x-1/2 translate-y-1/4 bg-linear-to-tr opacity-30 transition-transform duration-300 ease-out sm:left-[calc(50%+10rem)] sm:w-288.75"
                :style="`clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%); transform: translate(${-mouseX}px, ${-mouseY}px)`"
            ></div>
        </div>
        <div class="mx-auto max-w-4xl text-center">
            <div class="hidden sm:mb-8 sm:flex sm:justify-center">
                <div
                    class="relative flex items-center gap-x-2 rounded-full px-3 py-1 text-sm/6 text-gray-600 ring-1 ring-gray-900/10 hover:ring-gray-900/20 dark:text-gray-400 dark:ring-white/10 dark:hover:ring-white/20"
                >
                    <IconAI
                        class="size-4 text-indigo-500 dark:text-indigo-400"
                    />
                    {{ $t('Optimized for AI-assisted development') }}
                    <a
                        href="https://saucebase-dev.github.io/docs/development/ai"
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
                {{ $t('Skip the Boilerplate.') }}
                <span class="text-primary block">{{
                    $t('Ship Your Product.')
                }}</span>
            </h1>

            <!-- Subheadline -->
            <p class="mb-8 text-2xl text-gray-600 dark:text-gray-300">
                {{
                    $t(
                        'An open-source Laravel boilerplate with authentication, billing, and an admin panel built in. Modular, customizable, production-ready. Clone it, own it, ship it.',
                    )
                }}
            </p>

            <!-- Action Buttons -->
            <div
                class="flex flex-col items-center justify-center gap-4 sm:flex-row"
            >
                <!-- Primary CTA -->
                <Link
                    v-if="hasAuthModule && !user"
                    :href="route('register')"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary inline-flex items-center justify-center rounded-full px-8 py-4 text-lg font-semibold transition-all duration-200 hover:scale-105 focus:ring-2 focus:ring-offset-2 focus:outline-hidden dark:focus:ring-offset-gray-950"
                >
                    <IconUserPlus class="mr-2 h-5 w-5" />
                    {{ $t('Get Started Free') }}
                </Link>

                <!-- Dashboard Button (if logged in) -->
                <Link
                    v-if="hasDashboardRoute && user"
                    :href="route('dashboard')"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary inline-flex items-center justify-center rounded-full px-8 py-4 text-lg font-semibold transition-all duration-200 hover:scale-105 focus:ring-2 focus:ring-offset-2 focus:outline-hidden dark:focus:ring-offset-gray-950"
                >
                    <IconDashboard class="mr-2 h-5 w-5" />
                    {{ $t('Go to Dashboard') }}
                </Link>

                <!-- GitHub Button -->
                <a
                    href="https://github.com/saucebase-dev/saucebase"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="focus:ring-primary inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-8 py-4 text-lg font-semibold text-gray-700 transition-all duration-200 hover:scale-105 hover:bg-gray-50 focus:ring-2 focus:ring-offset-2 focus:outline-hidden dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-950"
                >
                    <IconGitHub class="mr-2 h-5 w-5" />
                    {{ $t('View on GitHub') }}
                </a>
            </div>

            <!-- Trust Bar -->
            <div class="mt-10 flex flex-wrap items-center justify-center gap-2">
                <span
                    v-for="label in [
                        'Laravel 13',
                        'Filament 5',
                        'Tailwind 4',
                        'Vue 3',
                    ]"
                    :key="label"
                    class="inline-flex items-center rounded-full border border-gray-900/10 bg-gray-900/5 px-4 py-1.5 text-sm font-semibold text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-300"
                >
                    {{ label }}
                </span>
            </div>

            <!-- Docs link -->
            <p
                class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400"
            >
                {{ $t('Explore everything that comes out of the box') }},
                <a
                    href="https://saucebase-dev.github.io/docs/what-is-saucebase"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="font-medium text-indigo-600 underline underline-offset-2 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                    >{{ $t('see all features') }} &rarr;</a
                >
            </p>
        </div>
    </main>
</template>
