<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
    modelValue?: string | number;
}>();

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
    (e: 'change', event: Event): void;
}>();
</script>

<template>
    <Input
        data-slot="input-group-control"
        :model-value="props.modelValue"
        :class="
            cn(
                'flex-1 rounded-none border-0 bg-transparent shadow-none focus-visible:ring-0 dark:bg-transparent',
                props.class,
            )
        "
        @update:model-value="emits('update:modelValue', $event)"
        @change="emits('change', $event)"
    />
</template>
