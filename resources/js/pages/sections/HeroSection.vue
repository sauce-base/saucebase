<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import IconGitHub from '~icons/heroicons/code-bracket';
import IconAI from '~icons/heroicons/sparkles';
import IconDashboard from '~icons/heroicons/squares-2x2';
import IconUserPlus from '~icons/heroicons/user-plus';

import { modules } from '@/composables/useModules';
import { usePage } from '@inertiajs/vue3';
import { computed, inject } from 'vue';

import { route as ziggyRoute } from 'ziggy-js';
const route = inject<typeof ziggyRoute>('route')!;

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const hasAuthModule = computed(() => modules().has('auth'));
const hasDashboardRoute = computed(() => route().has('dashboard'));
</script>

<template>
    <main
        class="bg-background/50 relative flex flex-1 flex-col items-center justify-center px-6 py-24 sm:py-32"
    >
        <!-- Top gradient blob -->
        <div
            class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80"
            aria-hidden="true"
        >
            <div
                class="from-secondary to-primary relative left-[calc(50%-11rem)] aspect-1155/678 w-144.5 -translate-x-1/2 rotate-30 bg-linear-to-tr opacity-30 transition-transform duration-300 ease-out sm:left-[calc(50%-30rem)] sm:w-288.75"
                :style="`clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%);)`"
            ></div>
        </div>

        <!-- Bottom gradient blob -->
        <div
            class="pointer-events-none absolute inset-x-0 right-0 -z-10 transform-gpu overflow-hidden blur-3xl"
            aria-hidden="true"
        >
            <div
                class="from-secondary to-primary relative left-[calc(50%-10rem)] aspect-1155/678 w-144.5 -translate-x-1/2 translate-y-1/4 bg-linear-to-tr opacity-30 transition-transform duration-300 ease-out sm:left-[calc(50%+10rem)] sm:w-288.75"
                :style="`clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%);)`"
            ></div>
        </div>
        <div class="mx-auto max-w-4xl text-center">
            <!-- Logo -->
            <!-- <div class="mb-12 flex justify-center">
                <AppLogo showText size="xxl" showSubtitle centered />
            </div> -->

            <div class="hidden sm:mb-8 sm:flex sm:justify-center">
                <div
                    class="text-muted-foreground ring-foreground/30 hover:ring-border/90 relative flex items-center gap-x-2 rounded-xl px-3 py-1 text-sm/6 ring-1"
                >
                    <IconAI class="size-4" />
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
            <p class="text-muted-foreground mb-8 text-2xl">
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
                    class="bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary focus:ring-offset-background inline-flex items-center justify-center rounded-xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:scale-105 focus:ring-2 focus:ring-offset-2 focus:outline-hidden"
                >
                    <IconUserPlus class="mr-2 h-5 w-5" />
                    {{ $t('Get Started Free') }}
                </Link>

                <!-- Dashboard Button (if logged in) -->
                <Link
                    v-if="hasDashboardRoute && user"
                    :href="route('dashboard')"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary focus:ring-offset-background inline-flex items-center justify-center rounded-xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:scale-105 focus:ring-2 focus:ring-offset-2 focus:outline-hidden"
                >
                    <IconDashboard class="mr-2 h-5 w-5" />
                    {{ $t('Go to Dashboard') }}
                </Link>

                <!-- GitHub Button -->
                <a
                    href="https://github.com/saucebase-dev/saucebase"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="focus:ring-primary border-border bg-background text-foreground hover:bg-accent focus:ring-offset-background inline-flex items-center justify-center rounded-xl border px-8 py-4 text-lg font-semibold transition-all duration-200 hover:scale-105 focus:ring-2 focus:ring-offset-2 focus:outline-hidden"
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
                    class="border-border/30 bg-foreground/5 text-muted-foreground inline-flex items-center rounded-xl border px-4 py-1.5 text-sm font-semibold"
                >
                    {{ label }}
                </span>
            </div>

            <!-- Docs link -->
            <p class="text-muted-foreground mt-4 text-center text-sm">
                {{ $t('Explore everything that comes out of the box') }},
                <a
                    href="https://saucebase-dev.github.io/docs/what-is-saucebase"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-primary font-medium underline-offset-4 hover:underline"
                    >{{ $t('see all features') }}</a
                >
            </p>
        </div>
    </main>
</template>
