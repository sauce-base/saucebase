<script setup lang="ts">
import type { SidebarProps } from '@/components/ui/sidebar/index';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar/index';
import type { User } from '@/types';
import type { Navigation } from '@/types/navigation';
import { hasGlobalComponent } from '@/lib/globalComponents';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppBrand from './AppBrand.vue';
import GlobalComponents from './GlobalComponents.vue';
import NavGroup from './ui/navigation/NavGroup.vue';
import NavUser from './ui/navigation/NavUser.vue';

withDefaults(defineProps<SidebarProps>(), {
    collapsible: 'icon',
    variant: 'inset',
    class: 'bg-transparent',
});

const page = usePage<{ navigation: Navigation; auth: { user: User } }>();

// Always show main navigation in main sidebar
const items = computed(() => page.props.navigation?.main || []);
const userItems = computed(() => page.props.navigation?.user || []);
const secondaryItems = computed(() => page.props.navigation?.secondary || []);
const user = computed(() => page.props.auth?.user);

// A module may own this block instead. Registration happens at import time, so this is
// settled before the first render.
const brandIsClaimed = hasGlobalComponent('sidebar-brand');
</script>

<template>
    <Sidebar
        :variant="variant"
        :collapsible="collapsible"
        data-sidebar="sidebar"
    >
        <SidebarHeader data-testid="sidebar-header">
            <GlobalComponents v-if="brandIsClaimed" position="sidebar-brand" />
            <AppBrand v-else />
        </SidebarHeader>

        <SidebarContent data-sidebar="content">
            <NavGroup :items="items" />
            <NavGroup :items="secondaryItems" class="mt-auto" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser v-if="user" :user="user" :items="userItems" />
        </SidebarFooter>
    </Sidebar>
</template>
