<script setup lang="ts">
import { cn } from '@/lib/utils';
import { reactiveOmit } from '@vueuse/core';
import { Plus } from 'lucide-vue-next';
import type { NumberFieldIncrementProps } from 'reka-ui';
import { NumberFieldIncrement, useForwardProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';

const props = defineProps<
    NumberFieldIncrementProps & { class?: HTMLAttributes['class'] }
>();

const delegatedProps = reactiveOmit(props, 'class');

const forwarded = useForwardProps(delegatedProps);
</script>

<template>
    <NumberFieldIncrement
        data-slot="increment"
        v-bind="forwarded"
        :class="
            cn(
                'absolute top-1/2 right-0 -translate-y-1/2 p-3 disabled:cursor-not-allowed disabled:opacity-20',
                props.class,
            )
        "
    >
        <slot>
            <Plus class="h-4 w-4" />
        </slot>
    </NumberFieldIncrement>
</template>
