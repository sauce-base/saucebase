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
        <div class="relative mx-auto max-w-7xl px-6 lg:px-8">
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
                class="bg-background mx-auto mt-8 -mb-60 h-130 max-w-5xl rounded-xl bg-[url(/images/screenshots/admin-dashboard-light.png)] mask-b-from-20% mask-b-to-60% bg-cover shadow-lg/90 xl:h-160 dark:bg-[url(/images/screenshots/admin-dashboard-dark.png)]"
            />

            <!-- 2-Column Features Grid -->
            <div class="relative z-10 mx-auto max-w-2xl lg:max-w-4xl">
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
