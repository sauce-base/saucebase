<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ButtonGroup } from '@/components/ui/button-group';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useColorMode } from '@vueuse/core';
import { computed, ref } from 'vue';
import IconAuto from '~icons/fluent/dark-theme-20-filled';
import IconMoon from '~icons/heroicons/moon';
import IconSun from '~icons/heroicons/sun';

interface Props {
    /**
     * Display mode - 'standalone' for main menu, 'submenu' for nested dropdown
     */
    mode?: 'standalone' | 'submenu';
    /**
     * Custom trigger class for standalone mode
     */
    triggerClass?: string;
    /**
     * Disable animated theme transitions using View Transitions API
     */
    disableAnimation?: boolean;
    /**
     * Render as an inline ButtonGroup instead of a dropdown
     */
    inline?: boolean;
    /**
     * Hide the Auto/Device theme option
     */
    hideDevice?: boolean;
    /**
     * Makes the inline ButtonGroup full-width with equal-width buttons
     */
    fullWidth?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'standalone',
    triggerClass:
        'flex items-center rounded-lg p-2 text-muted-foreground transition-colors duration-200 hover:bg-accent hover:text-accent-foreground',
});

const colorMode = useColorMode({
    emitAuto: true,
});

const triggerRef = ref<HTMLElement | null>(null);

const themes = [
    { code: 'light', name: 'Light', icon: IconSun },
    { code: 'dark', name: 'Dark', icon: IconMoon },
    { code: 'auto', name: 'Device', icon: IconAuto },
] as const;

const visibleThemes = computed(() =>
    props.hideDevice ? themes.filter((t) => t.code !== 'auto') : [...themes],
);

const switchTheme = async (
    themeCode: 'light' | 'dark' | 'auto',
    triggerEl?: HTMLElement,
) => {
    // Check if browser supports View Transitions API
    if (
        props.disableAnimation ||
        !document.startViewTransition ||
        window.matchMedia('(prefers-reduced-motion: reduce)').matches
    ) {
        // Fallback: Just change the theme without animation
        colorMode.value = themeCode;
        return;
    }

    let x = 0;
    let y = 0;

    const el = triggerEl ?? triggerRef.value;
    if (el && typeof el.getBoundingClientRect === 'function') {
        const rect = el.getBoundingClientRect();
        x = rect.left + rect.width / 2;
        y = rect.top + rect.height / 2;
    }

    // Calculate the radius needed to cover the entire viewport
    const endRadius = Math.hypot(
        Math.max(x, innerWidth - x),
        Math.max(y, innerHeight - y),
    );

    // Start view transition
    const transition = document.startViewTransition(() => {
        colorMode.value = themeCode;
    });

    // Wait for transition to be ready
    await transition.ready;

    // Animate the clip-path
    document.documentElement.animate(
        {
            clipPath: [
                `circle(0px at ${x}px ${y}px)`,
                `circle(${endRadius}px at ${x}px ${y}px)`,
            ],
        },
        {
            duration: 500,
            easing: 'ease-in-out',
            pseudoElement: '::view-transition-new(root)',
        },
    );
};

const currentTheme = computed(
    () => themes.find((theme) => theme.code === colorMode.value) || themes[0],
);

function handleInlineClick(
    themeCode: 'light' | 'dark' | 'auto',
    event: MouseEvent,
) {
    switchTheme(themeCode, event.currentTarget as HTMLElement);
}
</script>

<template>
    <!-- Inline Mode (ButtonGroup) -->
    <ButtonGroup v-if="inline" :class="{ 'w-full': fullWidth }">
        <Button
            v-for="theme in visibleThemes"
            :key="theme.code"
            :variant="colorMode === theme.code ? 'default' : 'outline'"
            size="sm"
            :class="[
                { 'flex-1': fullWidth },
                { 'font-semibold': colorMode === theme.code },
            ]"
            :data-testid="`color-mode-${theme.code}`"
            :aria-label="$t(theme.name)"
            @click="handleInlineClick(theme.code, $event)"
        >
            <component :is="theme.icon" class="size-4" />
            {{ $t(theme.name) }}
        </Button>
    </ButtonGroup>

    <!-- Standalone Mode (Landing Page) -->
    <DropdownMenu v-else-if="mode === 'standalone'" :modal="false">
        <DropdownMenuTrigger as-child>
            <button
                ref="triggerRef"
                :class="props.triggerClass"
                :aria-label="$t('Toggle theme')"
            >
                <slot name="trigger" :current-theme="currentTheme">
                    <component :is="currentTheme.icon" class="size-5" />
                </slot>
            </button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="min-w-40">
            <DropdownMenuItem
                v-for="theme in visibleThemes"
                :key="theme.code"
                @click="switchTheme(theme.code)"
                :class="{
                    'bg-accent text-accent-foreground':
                        colorMode === theme.code,
                }"
            >
                <component :is="theme.icon" class="size-4" />
                {{ $t(theme.name) }}
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>

    <!-- Submenu Mode (NavUser) -->
    <DropdownMenuSub v-else>
        <DropdownMenuSubTrigger
            ref="triggerRef"
            class="[&>svg]:text-muted-foreground [&>svg]:mr-2"
        >
            <slot name="submenu-trigger" :current-theme="currentTheme">
                <component :is="currentTheme.icon" class="size-4" />
                {{ $t('Theme') }}
            </slot>
        </DropdownMenuSubTrigger>
        <DropdownMenuSubContent>
            <DropdownMenuItem
                v-for="theme in visibleThemes"
                :key="theme.code"
                @click="switchTheme(theme.code)"
                :class="{ 'bg-accent': colorMode === theme.code }"
            >
                <component :is="theme.icon" class="size-4" />
                {{ $t(theme.name) }}
            </DropdownMenuItem>
        </DropdownMenuSubContent>
    </DropdownMenuSub>
</template>
