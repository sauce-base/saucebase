<script setup lang="ts">
import {
    InputGroup,
    InputGroupAddon,
    InputGroupButton,
    InputGroupInput,
} from '@/components/ui/input-group';
import { computed } from 'vue';
import IconEyedropper from '~icons/fa-solid/eye-dropper';
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

const iconColor = computed(() => {
    if (!model.value) return '#ffffff';
    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = 1;
    const ctx = canvas.getContext('2d')!;
    ctx.fillStyle = model.value;
    ctx.fillRect(0, 0, 1, 1);
    const [r, g, b] = ctx.getImageData(0, 0, 1, 1).data;
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance > 0.6 ? '#000000' : '#ffffff';
});
</script>

<template>
    <InputGroup class="group/input border-border rounded-full">
        <!-- Left: color swatch + label -->
        <InputGroupAddon align="inline-start">
            <ColorPickerPopover v-model="model">
                <InputGroupButton
                    size="icon-xs"
                    :aria-label="$t(`Open ${props.label} color picker`)"
                >
                    <span
                        class="border-border/50 relative size-6 cursor-pointer rounded-full border"
                        :style="`background: linear-gradient(${model}, ${model}), repeating-conic-gradient(#aaa 0% 25%, white 0% 50%) 0 0 / 8px 8px`"
                    >
                        <IconEyedropper
                            class="absolute inset-0 m-auto size-3 opacity-0 transition-opacity group-focus-within/input:opacity-70 group-hover/input:opacity-70"
                            :style="`color: ${iconColor}`"
                        />
                    </span>
                </InputGroupButton>
            </ColorPickerPopover>
            <span
                class="text-muted-foreground mr-1 min-w-20 border-r py-2 pr-4 text-[11px]"
            >
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
