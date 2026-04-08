<script setup lang="ts">
import {
    InputGroup,
    InputGroupAddon,
    InputGroupButton,
    InputGroupInput,
} from '@/components/ui/input-group';
import ColorPickerPopover from './ColorPickerPopover.vue';
import SyncToggle from './LinkToggle.vue';

const props = defineProps<{
    label: string;
    displayText?: string;
    testId?: string;
}>();

const model = defineModel<string>({ default: '' });
const synced = defineModel<boolean>('synced', { default: false });

function onInputChange(e: Event) {
    const val = (e.target as HTMLInputElement).value.trim();
    if (!val) return;
    model.value = val;
}
</script>

<template>
    <InputGroup class="rounded-full border-border">
        <!-- Left: color swatch + label -->
        <InputGroupAddon align="inline-start">
            <ColorPickerPopover v-model="model">
                <InputGroupButton
                    size="icon-xs"
                    :aria-label="$t(`Open ${props.label} color picker`)"
                >
                    <span
                        class="border-border/50 size-6 cursor-pointer rounded-full border shadow-sm"
                        :style="`background: linear-gradient(${model}, ${model}), repeating-conic-gradient(#aaa 0% 25%, white 0% 50%) 0 0 / 8px 8px`"
                    />
                </InputGroupButton>
            </ColorPickerPopover>
            <span class="text-muted-foreground mr-1 min-w-20 border-r py-2 pr-4 text-[11px]">
                {{ props.label }}
            </span>
        </InputGroupAddon>

        <!-- Hex / display value -->
        <InputGroupInput
            :model-value="props.displayText ?? model"
            :data-testid="props.testId"
            :name="props.testId"
            class="font-mono text-sm"
            @change="onInputChange"
        />

        <!-- Right: sync toggle -->
        <InputGroupAddon align="inline-end" class="relative pl-3">
            <SyncToggle
                v-model="synced"
                :tooltip="$t('Link across light/dark modes')"
                :tooltip-active="$t('Synced across modes — click to unlink')"
                class="-mr-1"
            />
        </InputGroupAddon>
    </InputGroup>
</template>
