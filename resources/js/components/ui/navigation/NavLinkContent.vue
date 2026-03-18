<script setup lang="ts">
import type { MenuBadge } from '@/types/navigation';
import { computed } from 'vue';
import IconExternalLink from '~icons/lucide/arrow-up-right';
import NavBadge from './NavBadge.vue';
import NavIcon from './NavIcon.vue';

const props = defineProps<{
    slug?: string;
    icon?: string | null;
    title: string;
    badge?: MenuBadge | null;
    showExternalIcon?: boolean;
}>();

// Badge spacing logic:
// - If there's a badge and no external icon: badge gets ml-auto (pushes to right)
// - If there's a badge and external icon: badge gets ml-auto mr-1 (pushes to right with spacing for icon)
// - If there's no badge but external icon: we need a spacer element with ml-auto
const badgeClass = computed(() => {
    if (!props.badge) return '';
    return props.showExternalIcon ? 'ml-auto mr-1' : 'ml-auto';
});

// Need a spacer when there's external icon but no badge
const needsSpacer = computed(() => props.showExternalIcon && !props.badge);
</script>

<template>
    <NavIcon :icon="icon" />
    <span>{{ $t(title) }}</span>
    <NavBadge :config="badge ?? null" :class="badgeClass" />
    <span v-if="needsSpacer" class="ml-auto" />
    <IconExternalLink v-if="showExternalIcon" class="size-4" />
</template>
