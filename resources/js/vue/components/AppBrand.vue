<script setup lang="ts">
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useSettings } from '@/composables/useSettings';
import { Link } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';

/**
 * The application's own mark at the top of the sidebar.
 *
 * What fills the `sidebar-brand` slot when no module claims it. A module with something
 * better to put there registers over it, so core never needs to know whether any
 * particular module is installed.
 */
const settings = useSettings();
</script>

<template>
    <SidebarMenu data-testid="app-brand">
        <SidebarMenuItem>
            <SidebarMenuButton size="lg" as-child>
                <Link href="/dashboard">
                    <div
                        class="text-sidebar-primary-foreground flex size-8 items-center justify-center rounded-lg p-0"
                    >
                        <AppLogo size="sm" />
                    </div>
                    <div class="grid flex-1 text-left text-sm leading-tight">
                        <span
                            class="truncate font-medium"
                            data-testid="app-brand-name"
                        >
                            {{ settings.general.site_name }}
                        </span>
                    </div>
                </Link>
            </SidebarMenuButton>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
