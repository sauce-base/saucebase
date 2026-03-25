<script setup lang="ts">
import {
    Component,
    GitCommitHorizontal,
    LayoutDashboard,
    Monitor,
    Palette,
    Route,
    ShieldCheck,
    Terminal,
    TestTube2,
    Zap,
} from 'lucide-vue-next';

const features = [
    {
        name: 'One-command setup',
        description: 'Docker, SSL, DB, assets, running in minutes',
        icon: Terminal,
    },
    {
        name: 'Hot reload (HMR)',
        description: 'Instant browser updates with no page refresh',
        icon: Zap,
    },
    {
        name: 'Type-safe routes',
        description: 'Ziggy generates TypeScript helpers from Laravel routes',
        icon: Route,
    },
    {
        name: 'SSR opt-in',
        description: 'Per-page server-side rendering for SEO',
        icon: Monitor,
    },
    {
        name: 'Filament admin',
        description: 'Full admin panel included out of the box',
        icon: LayoutDashboard,
    },
    {
        name: 'Dark / light mode',
        description: 'Theme switching built in',
        icon: Palette,
    },
    {
        name: 'shadcn-vue UI',
        description: 'Pre-integrated component library',
        icon: Component,
    },
    {
        name: 'PHPStan level 5',
        description: 'Static analysis on every commit',
        icon: ShieldCheck,
    },
    {
        name: 'Playwright E2E',
        description: 'End-to-end tests pre-configured',
        icon: TestTube2,
    },
    {
        name: 'Commitlint',
        description: 'Conventional commits enforced',
        icon: GitCommitHorizontal,
    },
];
</script>

<template>
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
                class="mx-auto grid max-w-2xl grid-cols-1 gap-x-8 gap-y-16 sm:gap-y-20 lg:mx-0 lg:max-w-none lg:grid-cols-5"
            >
                <div class="lg:col-span-3 lg:pt-4 lg:pr-8">
                    <div class="lg:max-w-none">
                        <h2
                            class="text-2xl font-semibold text-indigo-600 dark:text-indigo-400"
                        >
                            {{ $t('Built for developers') }}
                        </h2>
                        <p
                            class="mt-2 text-4xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-5xl dark:text-white"
                        >
                            {{ $t('Everything configured. Nothing missing.') }}
                        </p>
                        <p
                            class="mt-6 text-lg/8 text-gray-700 dark:text-gray-300"
                        >
                            {{
                                $t(
                                    'A production-ready foundation with the architecture, tooling, and philosophy to support your product long-term.',
                                )
                            }}
                        </p>

                        <div
                            class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2"
                        >
                            <div
                                v-for="feature in features"
                                :key="feature.name"
                                class="flex items-start gap-3"
                            >
                                <div
                                    class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-400"
                                >
                                    <component
                                        :is="feature.icon"
                                        class="size-4.5"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="text-base font-semibold text-gray-900 dark:text-white"
                                    >
                                        {{ $t(feature.name) }}
                                    </p>
                                    <p
                                        class="mt-0.5 text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ $t(feature.description) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <img
                    src="/images/screenshots/dashboard-dark.png"
                    :alt="$t('Saucebase dashboard screenshot')"
                    class="w-3xl max-w-none rounded-xl p-3 shadow-xl ring-1 ring-gray-400/10 not-dark:hidden sm:w-228 md:-ml-16 lg:col-span-2 lg:-ml-16 dark:ring-white/30"
                />
                <img
                    src="/images/screenshots/dashboard-light.png"
                    :alt="$t('Saucebase dashboard screenshot')"
                    class="w-3xl max-w-none rounded-xl p-3 shadow-2xl ring-1 shadow-black ring-gray-400/30 sm:w-228 md:-ml-16 lg:col-span-2 lg:-ml-16 dark:hidden dark:ring-white/30"
                />
            </div>
        </div>
    </div>
</template>
