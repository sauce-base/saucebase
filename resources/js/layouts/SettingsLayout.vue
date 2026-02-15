<script setup lang="ts">
import SettingsMobileMenu from '@/components/SettingsMobileMenu.vue';
import SettingsSidebar from '@/components/SettingsSidebar.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    title: string;
}>();
</script>

<template>
    <Head :title="$t(title)" />
    <AppLayout :title="title">
        <div class="flex h-full">
            <SettingsSidebar
                collapsible="none"
                class="w-56 border-r bg-transparent"
            />

            <!-- Page Content on the right -->
            <div class="flex-1 p-4">
                <div class="mx-auto w-full max-w-7xl">
                    <div class="flex h-full flex-1 flex-col">
                        <div
                            v-if="$slots.header"
                            class="mb-6 flex items-center"
                        >
                            <!-- Mobile settings menu button (mobile only) -->
                            <SettingsMobileMenu />
                            <slot name="header" />
                        </div>
                        <SettingsMobileMenu v-else class="mb-6" />
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
