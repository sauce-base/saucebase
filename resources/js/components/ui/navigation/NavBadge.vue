<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import type { MenuBadge } from '@/types/navigation';
import { computed } from 'vue';

const props = defineProps<{
    config: MenuBadge | null;
}>();

// Check if this is a dot badge (no content)
const isDotBadge = computed(() => !props.config?.content);

// Get variant-based color for dot badges
const dotColor = computed(() => {
    const variant = props.config?.variant;

    switch (variant) {
        case 'destructive':
            return 'bg-destructive';
        case 'secondary':
            return 'bg-secondary';
        case 'outline':
            return 'bg-border';
        default:
            return 'bg-primary';
    }
});
</script>

<template>
    <!-- Dot badge - just a small circle -->
    <span
        v-if="config && isDotBadge"
        class="size-2 rounded-xl"
        :class="[dotColor, config.class]"
    />
    <!-- Content badge - use Badge component -->
    <Badge v-else-if="config" :variant="config.variant" :class="config.class">
        {{ config.content }}
    </Badge>
</template>
