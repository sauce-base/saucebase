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
            'Inertia.js 3',
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
    <div id="stack" class="border-border bg-muted/50 border-t py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <!-- Header -->
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-primary text-2xl font-semibold">
                    {{ $t('No surprises') }}
                </h2>
                <p
                    class="text-foreground mt-2 text-4xl font-semibold tracking-tight text-pretty sm:text-5xl"
                >
                    {{ $t('Boring technology. Exceptional results.') }}
                </p>
                <p class="text-muted-foreground mt-6 text-lg/8">
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
                    class="border-border bg-card rounded-xl border p-6 shadow-lg hover:shadow-xl"
                >
                    <!-- Category header -->
                    <div class="mb-4 flex items-center gap-2">
                        <div
                            class="bg-muted flex size-7 items-center justify-center rounded-lg"
                        >
                            <component
                                :is="group.icon"
                                class="text-muted-foreground size-3.5"
                                aria-hidden="true"
                            />
                        </div>
                        <h3
                            class="text-foreground text-sm font-semibold tracking-wide uppercase"
                        >
                            {{ $t(group.label) }}
                        </h3>
                    </div>

                    <!-- Pills -->
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="item in group.items"
                            :key="item"
                            class="border-border bg-muted/50 text-muted-foreground inline-flex items-center rounded-xl border px-2.5 py-1 text-xs font-medium"
                        >
                            {{ $t(item) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
