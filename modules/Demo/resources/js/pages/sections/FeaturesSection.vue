<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import {
    BookOpen,
    Braces,
    Component,
    FlaskConical,
    Globe,
    LayoutDashboard,
    Navigation,
    Paintbrush,
    Terminal,
    TestTube2,
} from 'lucide-vue-next';

const features = [
    {
        name: () => trans('One-command setup'),
        description: () => trans('Docker, SSL, MySQL, Redis, Mailpit — one command and you\'re running.'),
        icon: Terminal,
    },
    {
        name: () => trans('Full TypeScript'),
        description: () => trans('PHP enums and DTOs auto-generate .d.ts types. Type-safe routes via Ziggy.'),
        icon: Braces,
    },
    {
        name: () => trans('Theming system'),
        description: () => trans('15 built-in themes, a live visual editor, custom fonts, colors, and border radius.'),
        icon: Paintbrush,
    },
    {
        name: () => trans('Filament admin'),
        description: () => trans('Auto-discovered module plugins. Tables, forms, actions, and widgets — no manual wiring.'),
        icon: LayoutDashboard,
    },
    {
        name: () => trans('PHPUnit test suite'),
        description: () => trans('Feature and unit tests per module with factories, running in isolated in-memory SQLite.'),
        icon: FlaskConical,
    },
    {
        name: () => trans('Playwright E2E'),
        description: () => trans('Per-module E2E test projects, auto-discovered and pre-configured for Chromium.'),
        icon: TestTube2,
    },
    {
        name: () => trans('Module navigation'),
        description: () => trans('Structured, module-aware navigation and breadcrumbs registered per module.'),
        icon: Navigation,
    },
    {
        name: () => trans('shadcn-vue UI'),
        description: () => trans('40+ accessible Tailwind 4 components, copy-and-own, dark mode built-in.'),
        icon: Component,
    },
    {
        name: () => trans('Complete documentation'),
        description: () => trans('Full docs site with module guides, architecture docs, and AI-optimized CLAUDE.md files.'),
        icon: BookOpen,
    },
    {
        name: () => trans('SSR opt-in'),
        description: () => trans('Per-page server-side rendering for SEO. Public pages opt in, auth pages opt out.'),
        icon: Globe,
    },
];
</script>

<template>
    <div
        id="features"
        class="border-border bg-background/50 relative isolate overflow-hidden border-t py-24 sm:py-32"
    >
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <svg
                aria-hidden="true"
                class="absolute top-0 left-[max(50%,25rem)] h-256 w-512 -translate-x-1/2 mask-[radial-gradient(64rem_64rem_at_top,white,transparent)] stroke-gray-300/70 dark:stroke-gray-700/50"
            >
                <defs>
                    <pattern
                        id="features-pattern"
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
                    class="fill-primary/5 dark:fill-foreground/5 overflow-visible"
                >
                    <path
                        d="M-100.5 0h201v201h-201Z M699.5 0h201v201h-201Z M499.5 400h201v201h-201Z M-300.5 600h201v201h-201Z"
                        stroke-width="0"
                    />
                </svg>
                <rect
                    width="100%"
                    height="100%"
                    fill="url(#features-pattern)"
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
                        <h2 class="text-primary text-2xl font-semibold">
                            {{ $t('Built for developers') }}
                        </h2>
                        <p
                            class="text-foreground mt-2 text-4xl font-semibold tracking-tight text-pretty sm:text-5xl"
                        >
                            {{ $t('Everything configured. Nothing missing.') }}
                        </p>
                        <p class="text-muted-foreground mt-6 text-lg/8">
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
                                :key="feature.name()"
                                class="flex items-start gap-3"
                            >
                                <div
                                    class="bg-primary/10 text-primary mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-lg"
                                >
                                    <component
                                        :is="feature.icon"
                                        class="size-4.5"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="text-foreground text-base font-semibold"
                                    >
                                        {{ feature.name() }}
                                    </p>
                                    <p
                                        class="text-muted-foreground mt-0.5 text-sm"
                                    >
                                        {{ feature.description() }}
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
