<script setup lang="ts">
import {
    BarChart3,
    Box,
    ClipboardList,
    Image,
    ShieldCheck,
    Users,
} from 'lucide-vue-next';

const features = [
    {
        name: 'User management',
        description:
            'Create, edit, and manage user accounts with role assignments and permissions.',
        icon: Users,
    },
    {
        name: 'Product management',
        description:
            'Full CRUD for products with categories, pricing, and inventory tracking.',
        icon: Box,
    },
    {
        name: 'Dashboard analytics',
        description:
            'Real-time insights with customizable widgets and data visualization.',
        icon: BarChart3,
    },
    {
        name: 'Role & permissions',
        description:
            'Fine-grained access control with Spatie permissions integration.',
        icon: ShieldCheck,
    },
    {
        name: 'Activity logs',
        description:
            'Track all user actions and system events for audit compliance.',
        icon: ClipboardList,
    },
    {
        name: 'Media management',
        description:
            'Upload, organize, and manage files with built-in image optimization.',
        icon: Image,
    },
];
</script>

<template>
    <div
        id="admin"
        class="relative isolate overflow-hidden bg-white/50 py-24 dark:border-gray-800 dark:bg-gray-900/70"
    >
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <!-- Centered Header -->
            <div class="mx-auto max-w-2xl text-center">
                <h2
                    class="text-2xl font-semibold text-indigo-600 dark:text-indigo-400"
                >
                    {{ $t('Powerful admin panel') }}
                </h2>
                <p
                    class="mt-2 text-4xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-5xl dark:text-white"
                >
                    {{ $t('Manage everything with ease') }}
                </p>
                <p class="mt-6 text-lg/8 text-gray-600 dark:text-gray-400">
                    {{
                        $t(
                            'Filament provides a beautiful admin panel with resources, forms, tables, and widgets. Manage your entire application from one place.',
                        )
                    }}
                </p>
            </div>

            <!-- Centered Image with Fade Overlay -->
            <div
                class="relative mx-auto mt-8 h-130 max-w-5xl overflow-hidden p-8"
            >
                <img
                    src="/images/screenshots/admin-dashboard-light.png"
                    :alt="$t('Filament admin panel screenshot')"
                    class="w-full rounded-xl rounded-b-none bg-gray-50/50 p-4 shadow-xl ring-1 ring-gray-400/10 dark:hidden dark:bg-gray-500/10 dark:ring-white/20"
                />
                <img
                    src="/images/screenshots/admin-dashboard-dark.png"
                    :alt="$t('Filament admin panel screenshot')"
                    class="w-full rounded-xl rounded-b-none bg-gray-50/50 p-4 shadow-xl ring-1 ring-gray-400/10 not-dark:hidden dark:bg-gray-500/10 dark:ring-white/20"
                />
                <!-- Bottom fade overlay -->
                <div
                    class="pointer-events-none absolute inset-x-0 bottom-0 mx-8 h-14 rounded-b-none border-b bg-linear-to-t from-gray-900/50 to-transparent blur dark:from-white/15"
                />
                <div
                    class="absolute inset-x-0 bottom-0 mx-4 w-full border-b-14"
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
                        <dt
                            class="text-base/7 font-semibold text-gray-900 dark:text-white"
                        >
                            <div
                                class="absolute top-0 left-0 flex size-10 items-center justify-center rounded-full bg-indigo-600 dark:bg-indigo-500"
                            >
                                <component
                                    :is="feature.icon"
                                    class="size-6 text-white"
                                    aria-hidden="true"
                                />
                            </div>
                            {{ $t(feature.name) }}
                        </dt>
                        <dd
                            class="mt-2 text-base/7 text-gray-600 dark:text-gray-400"
                        >
                            {{ $t(feature.description) }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</template>
