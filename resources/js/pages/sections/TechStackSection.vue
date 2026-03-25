<script setup lang="ts">
import { Cloud, Monitor, Server, Wrench } from 'lucide-vue-next';
import { type Component } from 'vue';

const groups: { label: string; icon: Component; items: string[] }[] = [
    {
        label: 'Backend',
        icon: Server,
        items: [
            'Laravel 13',
            'PHP 8.4+',
            'Filament 5',
            'Spatie Permission',
            'Laravel Telescope',
            'Laravel Sanctum',
        ],
    },
    {
        label: 'Frontend',
        icon: Monitor,
        items: [
            'Vue 3 Composition API',
            'Inertia.js 2',
            'Tailwind CSS 4',
            'TypeScript',
            'shadcn-vue',
            'Vite + HMR',
        ],
    },
    {
        label: 'Tooling',
        icon: Wrench,
        items: [
            'PHPStan level 5',
            'Laravel Pint',
            'ESLint + Prettier',
            'PHPUnit 12',
            'Playwright E2E',
            'Commitlint',
        ],
    },
    {
        label: 'Infrastructure',
        icon: Cloud,
        items: [
            'Docker',
            'Nginx',
            'MySQL 8',
            'Redis',
            'Mailpit',
            'Wildcard SSL',
        ],
    },
];
</script>

<template>
    <div
        id="stack"
        class="border-t border-gray-200 bg-gray-50 py-24 sm:py-32 dark:border-gray-800 dark:bg-gray-900/40"
    >
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <!-- Header -->
            <div class="mx-auto max-w-2xl text-center">
                <h2
                    class="text-2xl font-semibold text-indigo-600 dark:text-indigo-400"
                >
                    {{ $t('No surprises') }}
                </h2>
                <p
                    class="mt-2 text-4xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-5xl dark:text-white"
                >
                    {{ $t('Boring technology. Exceptional results.') }}
                </p>
                <p class="mt-6 text-lg/8 text-gray-600 dark:text-gray-400">
                    {{
                        $t(
                            'No experimental tools, no new frameworks to learn. Just the proven Laravel ecosystem, configured for production from day one.',
                        )
                    }}
                </p>
            </div>

            <!-- Stack Grid -->
            <div
                class="mx-auto mt-16 grid max-w-5xl grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
            >
                <div
                    v-for="group in groups"
                    :key="group.label"
                    class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800/50"
                >
                    <!-- Category header -->
                    <div class="mb-4 flex items-center gap-2">
                        <div
                            class="flex size-7 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700"
                        >
                            <component
                                :is="group.icon"
                                class="size-3.5 text-gray-500 dark:text-gray-400"
                                aria-hidden="true"
                            />
                        </div>
                        <h3
                            class="text-sm font-semibold tracking-wide text-gray-900 uppercase dark:text-white"
                        >
                            {{ $t(group.label) }}
                        </h3>
                    </div>

                    <!-- Pills -->
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="item in group.items"
                            :key="item"
                            class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        >
                            {{ $t(item) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
