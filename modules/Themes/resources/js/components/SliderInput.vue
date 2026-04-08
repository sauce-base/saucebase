<script setup lang="ts">
import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput,
} from '@/components/ui/number-field';
import Slider from '@/components/ui/slider/Slider.vue';
import { computed } from 'vue';

defineProps<{
    label: string;
    unit?: string;
    min?: number;
    max?: number;
    step?: number;
    testId?: string;
}>();

const model = defineModel<number | string>({ default: 0 });

const numericValue = computed(() => Number(model.value));

const handleChange = (v: number | undefined) => {
    if (v !== undefined) model.value = v;
};
</script>

<template>
    <div class="flex items-center gap-3">
        <span class="text-foreground min-w-12 shrink-0 text-sm ml-1">
            {{
            label
        }}
        </span>
        <Slider
            class="flex-1"
            :min="min ?? 0"
            :max="max ?? 1"
            :step="step ?? 0.01"
            :model-value="[numericValue]"
            @update:model-value="(v) => v?.length && (model = v[0])"
        />
        <div
            class="border-input focus-within:border-ring focus-within:ring-ring/50 flex w-32 shrink-0 overflow-hidden rounded-md border shadow-xs transition-[color,box-shadow] focus-within:ring-[3px]"
        >
            <NumberField
                :model-value="numericValue"
                :min="min ?? 0"
                :max="max ?? 1"
                :step="step ?? 0.01"
                class="min-w-0 flex-1"
                @update:model-value="handleChange"
            >
                <NumberFieldContent>
                    <NumberFieldDecrement />
                    <NumberFieldInput
                        :data-testid="testId"
                        class="rounded-none border-0 bg-transparent shadow-none focus-visible:ring-0 dark:bg-transparent"
                    />
                    <NumberFieldIncrement />
                </NumberFieldContent>
            </NumberField>
            <span
                v-if="unit"
                class="text-muted-foreground border-input flex items-center border-l px-2 text-sm"
            >
                {{ unit }}
            </span>
        </div>
    </div>
</template>
