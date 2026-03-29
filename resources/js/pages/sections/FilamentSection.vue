<script setup lang="ts">
import {
    LayoutDashboard,
    MousePointerClick,
    Palette,
    Puzzle,
    Table2,
    WandSparkles,
} from 'lucide-vue-next';

const features = [
    {
        name: 'Tables & resources',
        description:
            'Full CRUD resources with sortable, filterable, searchable tables and bulk actions, built with minimal boilerplate.',
        icon: Table2,
    },
    {
        name: 'Schema-based forms',
        description:
            'Rich form builder with 30+ field types (text, select, date, file upload, repeaters, and more), all in PHP.',
        icon: WandSparkles,
    },
    {
        name: 'Actions & modals',
        description:
            'Contextual actions with confirmation dialogs, inline forms, and toast notifications built in.',
        icon: MousePointerClick,
    },
    {
        name: 'Widgets & stats',
        description:
            'Stats overviews, charts, and custom widgets to surface key metrics directly on your admin dashboard.',
        icon: LayoutDashboard,
    },
    {
        name: 'Dark mode & theming',
        description:
            'First-class dark mode and customizable design tokens to match your brand, no extra CSS required.',
        icon: Palette,
    },
    {
        name: 'Plugin architecture',
        description:
            'Modules register their own resources via Filament plugins, auto-discovered with no manual wiring.',
        icon: Puzzle,
    },
];
</script>

<template>
    <div
        id="admin"
        class="bg-background/50 relative isolate overflow-hidden py-24"
    >
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <!-- Centered Header -->
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-primary text-2xl font-semibold">
                    {{ $t('Powered by Filament 5') }}
                </h2>
                <p
                    class="text-foreground mt-2 text-4xl font-semibold tracking-tight text-pretty sm:text-5xl"
                >
                    {{ $t('A beautiful admin panel, included.') }}
                </p>
                <p class="text-muted-foreground mt-6 text-lg/8">
                    {{
                        $t(
                            'Filament gives you a production-ready admin panel with tables, forms, and actions. Each module registers its own resources, no manual wiring required.',
                        )
                    }}
                </p>
            </div>

            <!-- Centered Image with Fade Overlay -->
            <div
                class="relative mx-auto mt-8 max-w-5xl overflow-hidden rounded-2xl shadow-2xl xl:h-130"
            >
                <img
                    src="/images/screenshots/admin-dashboard-light.png"
                    :alt="$t('Filament admin panel screenshot')"
                    class="w-full rounded-xl rounded-b-none bg-gray-50/50 p-4 shadow-xl ring-gray-400/10 dark:hidden dark:bg-gray-500/10 dark:ring-white/20"
                />
                <img
                    src="/images/screenshots/admin-dashboard-dark.png"
                    :alt="$t('Filament admin panel screenshot')"
                    class="relative mx-auto mt-8 w-full max-w-5xl rounded-xl rounded-b-none bg-gray-50/50 p-4 shadow-xl ring-gray-400/10 not-dark:hidden dark:bg-gray-500/10 dark:ring-white/20"
                />
                <!-- Bottom fade overlay -->
                <div
                    class="pointer-events-none absolute inset-x-0 bottom-0 h-14 rounded-b-none border-b bg-linear-to-t from-gray-900/30 to-transparent blur dark:from-white/15"
                />
                <div
                    class="absolute inset-x-0 bottom-0 mx-auto w-full border-b-14"
                />
            </div>

            <!-- 2-Column Features Grid -->
            <div class="mx-auto mt-16 max-w-2xl lg:max-w-4xl">
                <dl
                    class="grid grid-cols-1 gap-x-8 gap-y-10 lg:grid-cols-2 lg:gap-y-16"
                >
                    <div
                        v-for="feature in features"
                        :key="feature.name"
                        class="relative pl-16"
                    >
                        <dt class="text-foreground text-base/7 font-semibold">
                            <div
                                class="bg-primary absolute top-0 left-0 flex size-10 items-center justify-center rounded-xl"
                            >
                                <component
                                    :is="feature.icon"
                                    class="text-primary-foreground size-6"
                                    aria-hidden="true"
                                />
                            </div>
                            {{ $t(feature.name) }}
                        </dt>
                        <dd class="text-muted-foreground mt-2 text-base/7">
                            {{ $t(feature.description) }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</template>
