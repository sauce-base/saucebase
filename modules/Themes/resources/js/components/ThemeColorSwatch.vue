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
        class="ring-offset-muted grid shrink-0 grid-cols-2 grid-rows-2 gap-0.5 p-1.5 shadow-xl ring-1 ring-black/10 ring-offset-2 dark:ring-white/10"
        :style="{
            backgroundColor: preview.background,
            borderRadius: radius ?? 'var(--radius)',
        }"
    >
        <!-- Primary circle -->
        <span
            class="size-2 shadow-sm ring-1 ring-black/10"
            :style="{
                backgroundColor: preview.primary ?? 'var(--primary)',
                borderRadius: radiusSm,
            }"
        />
        <!-- Secondary circle -->
        <span
            class="size-2 shadow-sm ring-1 ring-black/10"
            :style="{
                backgroundColor: preview.secondary ?? 'var(--secondary)',
                borderRadius: radiusSm,
            }"
        />
        <!-- Uppercase "A" — simulates heading/bold text -->
        <span
            class="flex size-2 items-center justify-center text-[12px] leading-none font-bold"
            :style="{ color: preview.foreground ?? 'var(--foreground)' }"
            >A</span
        >
        <!-- Lowercase "a" — simulates body text -->
        <span
            class="flex size-2 items-center justify-center text-[10px] leading-none opacity-70"
            :style="{ color: preview.foreground ?? 'var(--foreground)' }"
            >a</span
        >
    </span>
</template>
