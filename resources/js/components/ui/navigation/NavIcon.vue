<script setup lang="ts">
import { computed, type Component } from 'vue';
import IconCreditCard from '~icons/lucide/credit-card';
import IconHelpCircle from '~icons/lucide/help-circle';
import IconLogOut from '~icons/lucide/log-out';
import IconSettings from '~icons/lucide/settings';
import IconShieldCheck from '~icons/lucide/shield-check';
import IconSparkles from '~icons/lucide/sparkles';
import IconSquareTerminal from '~icons/lucide/square-terminal';
import IconUserCircle from '~icons/lucide/user-circle';
import IconGithub from '~icons/mdi/github';

const props = defineProps<{ icon?: string | null }>();

// NavigationIcon - Manual icon mapping
//
// This component maps icon slugs from backend navigation data to Vue icon components.
// Icons are registered manually in the iconMapping object below.
//
// Backend provides semantic slugs (e.g., 'settings', 'dashboard')
// Frontend decides which icon library to use for each slug.

const iconMapping: Record<string, Component> = {
    settings: IconSettings,
    dashboard: IconSquareTerminal,
    profile: IconUserCircle,
    logout: IconLogOut,
    github: IconGithub,
    admin: IconShieldCheck,
    documentation: IconHelpCircle,
    billing: IconCreditCard,
    upgrade: IconSparkles,
};

const iconComponent = computed<Component | null>(() => {
    if (!props.icon) {
        return null;
    }

    const icon = iconMapping[props.icon] || null;

    if (!icon && import.meta.env.DEV) {
        console.warn(
            `[NavigationIcon] Icon mapping missing for slug: "${props.icon}".\n` +
                `Add it to NavigationIcon.vue iconMapping.`,
        );

        return IconHelpCircle;
    }

    return icon;
});
</script>

<template>
    <component :is="iconComponent" v-if="iconComponent" />
</template>
