<script setup lang="ts">
import { computed } from 'vue';

interface Preview {
    background?: string;
    primary?: string;
    secondary?: string;
    foreground?: string;
}

const props = defineProps<{
    preview: Preview;
    radius?: string;
}>();

const radiusSm = computed(() => {
    if (!props.radius) return 'var(--radius-sm)';
    const r = parseFloat(props.radius);
    return `${Math.max(0, r - 0.125)}rem`;
});
</script>

<template>
    <span
        class="grid shrink-0 grid-cols-2 grid-rows-2 gap-0.5 p-1.5 ring-1 ring-black/10 dark:ring-white/10 ring-offset-2 ring-offset-muted shadow-xl"
        :style="{ backgroundColor: preview.background, borderRadius: radius ?? 'var(--radius)' }"
    >
        <!-- Primary circle -->
        <span
            class="size-2 ring-1 ring-black/10 shadow-sm"
            :style="{ backgroundColor: preview.primary ?? 'var(--primary)', borderRadius: radiusSm }"
        />
        <!-- Secondary circle -->
        <span
            class="size-2 ring-1 ring-black/10 shadow-sm"
            :style="{ backgroundColor: preview.secondary ?? 'var(--secondary)', borderRadius: radiusSm }"
        />
        <!-- Uppercase "A" — simulates heading/bold text -->
        <span
            class="flex size-2 items-center justify-center text-[12px] font-bold leading-none"
            :style="{ color: preview.foreground ?? 'var(--foreground)' }"
        >A</span>
        <!-- Lowercase "a" — simulates body text -->
        <span
            class="flex size-2 items-center justify-center text-[10px] leading-none opacity-70"
            :style="{ color: preview.foreground  ?? 'var(--foreground)'}"
        >a</span>
    </span>
</template>
