<script setup lang="ts">
import LanguageSelector from '@/components/LanguageSelector.vue';
import ThemeSelector from '@/components/ThemeSelector.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import NavigationIcon from '@/components/ui/navigation/NavIcon.vue';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import type { User } from '@/types';
import type { MenuBadge, MenuItem } from '@/types/navigation';
import { handleAction } from '@/utils/actionHandlers';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import IconChevronsUpDown from '~icons/lucide/chevrons-up-down';
import IconUserCircle from '~icons/lucide/user-circle';

const props = defineProps<{
    user: User;
    items: MenuItem[];
}>();

const { isMobile } = useSidebar();

const userInitials = computed(() => {
    return props.user.name
        .split(' ')
        .map((word) => word.charAt(0).toUpperCase())
        .slice(0, 2)
        .join('');
});

// Helper function to get badge configuration for an item
function getBadgeConfig(item: MenuItem): MenuBadge | null {
    if (!item.badge) return null;

    // If badge is true, return simple dot configuration
    if (item.badge === true) {
        return { content: undefined };
    }

    // Otherwise return the badge object
    return item.badge as MenuBadge;
}

function handleClick(item: MenuItem, event: MouseEvent) {
    if (item.action) {
        handleAction(item.action, event);
    }
}
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        data-testid="user-menu-trigger"
                        size="lg"
                        class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                    >
                        <Avatar class="h-8 w-8 rounded-lg">
                            <AvatarImage :src="user.avatar" :alt="user.name" />
                            <AvatarFallback class="rounded-lg">
                                {{ userInitials }}
                            </AvatarFallback>
                        </Avatar>
                        <div
                            class="grid flex-1 text-left text-sm leading-tight"
                        >
                            <span class="truncate font-medium">
                                {{ user.name }}
                            </span>
                            <span class="truncate text-xs">
                                {{ user.email }}
                            </span>
                        </div>
                        <IconChevronsUpDown class="ml-auto size-4" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    class="w-[--reka-dropdown-menu-trigger-width] min-w-56 rounded-lg"
                    :side="isMobile ? 'bottom' : 'right'"
                    align="end"
                    :side-offset="4"
                >
                    <DropdownMenuLabel class="p-0 font-normal">
                        <div
                            class="flex items-center gap-2 px-1 py-1.5 text-left text-sm"
                        >
                            <Avatar class="h-8 w-8 rounded-lg">
                                <AvatarImage
                                    :src="user.avatar"
                                    :alt="user.name"
                                />
                                <AvatarFallback class="rounded-lg">
                                    {{ userInitials }}
                                </AvatarFallback>
                            </Avatar>
                            <div
                                class="grid flex-1 text-left text-sm leading-tight"
                            >
                                <span class="truncate font-semibold">
                                    {{ user.name }}
                                </span>
                                <span class="truncate text-xs">
                                    {{ user.email }}
                                </span>
                            </div>
                        </div>
                    </DropdownMenuLabel>

                    <template v-if="route().has('settings.profile')">
                        <DropdownMenuSeparator />
                        <DropdownMenuItem as-child>
                            <Link :href="route('settings.profile')">
                                <IconUserCircle class="size-4" />
                                {{ $t('Profile') }}
                            </Link>
                        </DropdownMenuItem>
                    </template>

                    <DropdownMenuSeparator />

                    <!-- Fixed components (Language & Theme selectors) -->
                    <DropdownMenuGroup>
                        <LanguageSelector mode="submenu" />
                        <ThemeSelector mode="submenu" />
                    </DropdownMenuGroup>

                    <!-- Dynamic navigation items -->
                    <template v-if="items && items.length > 0">
                        <DropdownMenuSeparator />
                        <DropdownMenuGroup>
                            <DropdownMenuItem
                                v-for="item in items"
                                :key="item.id || item.title"
                                as-child
                                @click="
                                    item.action
                                        ? handleClick(item, $event)
                                        : undefined
                                "
                            >
                                <!-- Action button -->
                                <div
                                    v-if="item.action"
                                    :class="['cursor-pointer', item.class]"
                                >
                                    <NavigationIcon
                                        :icon="item.slug"
                                        :class="item.class && 'text-current'"
                                    />
                                    <span>{{ $t(item.title) }}</span>
                                    <Badge
                                        v-if="getBadgeConfig(item)"
                                        :variant="getBadgeConfig(item)?.variant"
                                        :class="getBadgeConfig(item)?.class"
                                        class="ml-auto"
                                    >
                                        <template
                                            v-if="getBadgeConfig(item)?.content"
                                        >
                                            {{ getBadgeConfig(item)?.content }}
                                        </template>
                                        <template v-else>
                                            <span
                                                class="size-1.5 rounded-full bg-current"
                                            ></span>
                                        </template>
                                    </Badge>
                                </div>
                                <!-- External link (regular anchor) -->
                                <a
                                    v-else-if="item.external === true"
                                    :href="item.url || '#'"
                                    :target="
                                        item.newPage ? '_blank' : undefined
                                    "
                                    :rel="
                                        item.newPage
                                            ? 'noopener noreferrer'
                                            : undefined
                                    "
                                    :class="item.class"
                                >
                                    <NavigationIcon
                                        :icon="item.slug"
                                        :class="item.class && 'text-current'"
                                    />
                                    <span>{{ $t(item.title) }}</span>
                                    <Badge
                                        v-if="getBadgeConfig(item)"
                                        :variant="getBadgeConfig(item)?.variant"
                                        :class="getBadgeConfig(item)?.class"
                                        class="ml-auto"
                                    >
                                        <template
                                            v-if="getBadgeConfig(item)?.content"
                                        >
                                            {{ getBadgeConfig(item)?.content }}
                                        </template>
                                        <template v-else>
                                            <span
                                                class="size-1.5 rounded-full bg-current"
                                            ></span>
                                        </template>
                                    </Badge>
                                </a>
                                <!-- Internal Inertia link -->
                                <Link
                                    v-else
                                    :href="item.url || '#'"
                                    :target="
                                        item.newPage ? '_blank' : undefined
                                    "
                                    :class="item.class"
                                >
                                    <NavigationIcon
                                        :icon="item.slug"
                                        :class="item.class && 'text-current'"
                                    />
                                    <span>{{ $t(item.title) }}</span>
                                    <Badge
                                        v-if="getBadgeConfig(item)"
                                        :variant="getBadgeConfig(item)?.variant"
                                        :class="getBadgeConfig(item)?.class"
                                        class="ml-auto"
                                    >
                                        <template
                                            v-if="getBadgeConfig(item)?.content"
                                        >
                                            {{ getBadgeConfig(item)?.content }}
                                        </template>
                                        <template v-else>
                                            <span
                                                class="size-1.5 rounded-full bg-current"
                                            ></span>
                                        </template>
                                    </Badge>
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuGroup>
                    </template>
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
