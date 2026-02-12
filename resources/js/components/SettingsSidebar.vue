<script setup lang="ts">
import { NavGroup } from '@/components/ui/navigation';
import type { SidebarProps } from '@/components/ui/sidebar';
import { Sidebar, SidebarContent, useSidebar } from '@/components/ui/sidebar';
import { PageProps } from '@/types';
import type { Navigation } from '@/types/navigation';
import { usePage } from '@inertiajs/vue3';
import { computed, provide } from 'vue';
import IconSettings from '~icons/lucide/settings';

const props = withDefaults(defineProps<SidebarProps>(), {
    collapsible: 'none',
    variant: 'inset',
    class: '',
});

const page = usePage<PageProps<{ navigation: Navigation }>>();
const { isMobile } = useSidebar();

// Show settings navigation
const items = computed(() => page.props.navigation?.settings || []);

// Disable tooltips for settings sidebar (it's never collapsible on desktop)
provide('showTooltip', false);
</script>

<template>
    <!-- Hide settings sidebar on mobile - use SettingsMobileMenu instead -->
    <Sidebar
        v-if="!isMobile"
        :variant="variant"
        :collapsible="collapsible"
        data-sidebar="settings-sidebar"
        :class="props.class"
        v-bind="$attrs"
    >
        <slot name="header">
            <div class="flex items-center px-3 pt-4 pb-4 text-lg font-semibold">
                <IconSettings class="mr-2 inline-block" />
                {{ $t('Settings') }}
            </div>
        </slot>
        <SidebarContent data-sidebar="content" class="mb-2">
            <NavGroup :items="items" />
        </SidebarContent>
    </Sidebar>
</template>
