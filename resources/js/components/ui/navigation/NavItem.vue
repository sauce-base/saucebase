<script setup lang="ts">
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Separator } from '@/components/ui/separator';
import {
    SidebarGroupLabel,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { handleAction } from '@/lib/navigation';
import type { MenuBadge, MenuItem } from '@/types/navigation';
import { Link } from '@inertiajs/vue3';
import { computed, inject } from 'vue';
import IconChevronRight from '~icons/lucide/chevron-right';
import NavLinkContent from './NavLinkContent.vue';

const props = defineProps<{
    item: MenuItem;
}>();

// Inject tooltip visibility from parent sidebar (defaults to true)
const showTooltip = inject<boolean>('showTooltip', true);

// Type detection
const isSeparator = computed(() => props.item.type === 'separator');
const isLabel = computed(() => props.item.type === 'label');
const isAction = computed(() => !!props.item.action);
const hasChildren = computed(() => !!props.item.children?.length);
const isExternal = computed(() => props.item.external === true);
const openInNewTab = computed(() => props.item.newPage === true);

// Active state - prefer server-side from Spatie, fallback to Ziggy
const isActive = computed(() => {
    // Use server-side active state if available
    if (props.item.active !== undefined) {
        return props.item.active;
    }

    // Fallback to client-side Ziggy route matching
    if (!props.item.route) return false;
    try {
        return route().current(props.item.route);
    } catch {
        return false;
    }
});

// Badge configuration
const badgeConfig = computed<MenuBadge | null>(() => {
    if (!props.item.badge) return null;

    // If badge is true, return simple dot configuration
    if (props.item.badge === true) {
        return { content: undefined };
    }

    // Otherwise return the badge object
    return props.item.badge as MenuBadge;
});

// Helper to get badge config for child items
function getChildBadgeConfig(child: MenuItem): MenuBadge | null {
    if (!child.badge) return null;
    return child.badge === true
        ? { content: undefined }
        : (child.badge as MenuBadge);
}

// Action handler
function handleClick(event: MouseEvent) {
    if (props.item.action) {
        handleAction(props.item.action, event);
    }
}
</script>

<template>
    <!-- Separator -->
    <Separator v-if="isSeparator" />

    <!-- Label -->
    <SidebarGroupLabel v-else-if="isLabel">
        {{ $t(item.title) }}
    </SidebarGroupLabel>

    <!-- Collapsible group with children -->
    <Collapsible
        v-else-if="hasChildren"
        as-child
        :default-open="isActive"
        class="group/collapsible"
    >
        <SidebarMenuItem>
            <CollapsibleTrigger as-child>
                <SidebarMenuButton
                    :tooltip="showTooltip ? $t(item.title) : undefined"
                    :class="item.class"
                >
                    <NavLinkContent
                        :slug="item.slug"
                        :icon="item.icon"
                        :title="item.title"
                        :badge="badgeConfig"
                    />
                    <IconChevronRight
                        class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                    />
                </SidebarMenuButton>
            </CollapsibleTrigger>
            <CollapsibleContent>
                <SidebarMenuSub>
                    <SidebarMenuSubItem
                        v-for="child in item.children"
                        :key="child.id || child.title"
                    >
                        <SidebarMenuSubButton
                            as-child
                            :is-active="
                                child.active !== undefined
                                    ? child.active
                                    : !!(
                                          child.route &&
                                          route().current(child.route)
                                      )
                            "
                        >
                            <!-- External link (regular anchor) -->
                            <a
                                v-if="child.external === true"
                                :href="child.url || '#'"
                                :target="child.newPage ? '_blank' : undefined"
                                :rel="
                                    child.newPage
                                        ? 'noopener noreferrer'
                                        : undefined
                                "
                                :class="child.class"
                            >
                                <NavLinkContent
                                    :slug="child.slug"
                                    :icon="child.icon"
                                    :title="child.title"
                                    :badge="getChildBadgeConfig(child)"
                                    :show-external-icon="child.newPage"
                                />
                            </a>
                            <!-- Internal Inertia link -->
                            <Link
                                v-else
                                :href="child.url || '#'"
                                :target="child.newPage ? '_blank' : undefined"
                                :class="child.class"
                            >
                                <NavLinkContent
                                    :slug="child.slug"
                                    :icon="child.icon"
                                    :title="child.title"
                                    :badge="getChildBadgeConfig(child)"
                                    :show-external-icon="child.newPage"
                                />
                            </Link>
                        </SidebarMenuSubButton>
                    </SidebarMenuSubItem>
                </SidebarMenuSub>
            </CollapsibleContent>
        </SidebarMenuItem>
    </Collapsible>

    <!-- Action button -->
    <SidebarMenuItem v-else-if="isAction">
        <SidebarMenuButton
            :tooltip="showTooltip ? $t(item.title) : undefined"
            :class="item.class"
            @click="handleClick"
        >
            <NavLinkContent
                :slug="item.slug"
                :icon="item.icon"
                :title="item.title"
                :badge="badgeConfig"
            />
        </SidebarMenuButton>
    </SidebarMenuItem>

    <!-- Link -->
    <SidebarMenuItem v-else>
        <SidebarMenuButton
            as-child
            :is-active="isActive"
            :tooltip="showTooltip ? $t(item.title) : undefined"
        >
            <!-- External link (regular anchor) -->
            <a
                v-if="isExternal"
                :href="item.url || '#'"
                :target="openInNewTab ? '_blank' : undefined"
                :rel="openInNewTab ? 'noopener noreferrer' : undefined"
                :class="item.class"
            >
                <NavLinkContent
                    :slug="item.slug"
                    :icon="item.icon"
                    :title="item.title"
                    :badge="badgeConfig"
                    :show-external-icon="openInNewTab"
                />
            </a>
            <!-- Internal Inertia link -->
            <Link
                v-else
                :href="item.url || '#'"
                :target="openInNewTab ? '_blank' : undefined"
                :class="item.class"
            >
                <NavLinkContent
                    :slug="item.slug"
                    :icon="item.icon"
                    :title="item.title"
                    :badge="badgeConfig"
                    :show-external-icon="openInNewTab"
                />
            </Link>
        </SidebarMenuButton>
    </SidebarMenuItem>
</template>
