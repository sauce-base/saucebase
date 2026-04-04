<script setup lang="ts">
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <Textarea
        data-slot="input-group-control"
        :class="
            cn(
                'flex-1 resize-none rounded-none border-0 bg-transparent py-3 shadow-none focus-visible:ring-0 dark:bg-transparent',
                props.class,
            )
        "
    />
</template>
