<script setup lang="ts">
import { Input } from '@/components/ui/input';
import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput,
} from '@/components/ui/number-field';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { computed, onUnmounted, ref, watch } from 'vue';
import IconChevronUpDown from '~icons/heroicons/chevron-up-down';
import IconEyedropper from '~icons/fa-solid/eye-dropper';
import IconPalette from '~icons/lucide/palette';
import IconTailwind from '~icons/mdi/tailwind';
import TailwindColorPicker from './TailwindColorPicker.vue';

const model = defineModel<string>({ default: '' });

// ── Tabs ───────────────────────────────────────────────────────────────────────

const activeTab = ref<'custom' | 'tailwind'>('custom');

// ── Color conversions ─────────────────────────────────────────────────────────

function hexToRgb(hex: string) {
    const clean = hex.replace('#', '');
    const full =
        clean.length === 3
            ? clean
                  .split('')
                  .map((c) => c + c)
                  .join('')
            : clean;
    return {
        r: parseInt(full.slice(0, 2), 16) || 0,
        g: parseInt(full.slice(2, 4), 16) || 0,
        b: parseInt(full.slice(4, 6), 16) || 0,
    };
}

function rgbToHex(r: number, g: number, b: number): string {
    return (
        '#' +
        [r, g, b]
            .map((v) =>
                Math.round(Math.min(255, Math.max(0, v)))
                    .toString(16)
                    .padStart(2, '0'),
            )
            .join('')
    );
}

function rgbToHsv(r: number, g: number, b: number) {
    r /= 255;
    g /= 255;
    b /= 255;
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    const d = max - min;
    let h = 0;
    if (d !== 0) {
        if (max === r) {
            h = ((g - b) / d) % 6;
        } else if (max === g) {
            h = (b - r) / d + 2;
        } else {
            h = (r - g) / d + 4;
        }
        h = h * 60;
        if (h < 0) {
            h += 360;
        }
    }
    return {
        h: Math.round(h),
        s: max === 0 ? 0 : (d / max) * 100,
        v: max * 100,
    };
}

function hsvToRgb(h: number, s: number, v: number) {
    s /= 100;
    v /= 100;
    const i = Math.floor(h / 60) % 6;
    const f = h / 60 - Math.floor(h / 60);
    const p = v * (1 - s);
    const q = v * (1 - f * s);
    const t = v * (1 - (1 - f) * s);
    let r = 0,
        g = 0,
        b = 0;
    switch (i) {
        case 0:
            r = v;
            g = t;
            b = p;
            break;
        case 1:
            r = q;
            g = v;
            b = p;
            break;
        case 2:
            r = p;
            g = v;
            b = t;
            break;
        case 3:
            r = p;
            g = q;
            b = v;
            break;
        case 4:
            r = t;
            g = p;
            b = v;
            break;
        case 5:
            r = v;
            g = p;
            b = q;
            break;
    }
    return {
        r: Math.round(r * 255),
        g: Math.round(g * 255),
        b: Math.round(b * 255),
    };
}

// ── Custom picker state ───────────────────────────────────────────────────────

const isOpen = ref(false);
const hue = ref(0);
const saturation = ref(100);
const brightness = ref(100);
const mode = ref<'rgb' | 'hex'>('rgb');
const gradientRef = ref<HTMLElement | null>(null);

/** Convert any valid CSS color string to #rrggbb by drawing it on a 1×1 canvas. */
function cssColorToHex(color: string): string {
    const canvas = document.createElement('canvas');
    canvas.width = 1;
    canvas.height = 1;
    const ctx = canvas.getContext('2d');
    if (!ctx) return '#000000';
    ctx.fillStyle = color;
    ctx.fillRect(0, 0, 1, 1);
    const [r, g, b] = ctx.getImageData(0, 0, 1, 1).data;
    return rgbToHex(r, g, b);
}

function initFromHex(color: string) {
    try {
        const hex = color.startsWith('#') ? color : cssColorToHex(color);
        const { r, g, b } = hexToRgb(hex);
        const { h, s, v } = rgbToHsv(r, g, b);
        hue.value = h;
        saturation.value = s;
        brightness.value = v;
    } catch {
        hue.value = 0;
        saturation.value = 0;
        brightness.value = 100;
    }
}

watch(model, (val) => {
    if (!isOpen.value) {
        initFromHex(val);
    }
});
watch(isOpen, (v) => {
    if (v) {
        initFromHex(model.value);
    }
});

const currentRgb = computed(() =>
    hsvToRgb(hue.value, saturation.value, brightness.value),
);
const currentHex = computed(() =>
    rgbToHex(currentRgb.value.r, currentRgb.value.g, currentRgb.value.b),
);
const hueHsl = computed(() => `hsl(${hue.value}, 100%, 50%)`);
const hexInput = ref('');
watch(
    currentHex,
    (v) => {
        hexInput.value = v.slice(1).toUpperCase();
    },
    { immediate: true },
);

function clamp(v: number, lo: number, hi: number) {
    return Math.max(lo, Math.min(hi, v));
}

function updateFromPointer(e: MouseEvent | TouchEvent) {
    if (!gradientRef.value) return;
    const rect = gradientRef.value.getBoundingClientRect();
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;
    saturation.value = clamp((clientX - rect.left) / rect.width, 0, 1) * 100;
    brightness.value =
        (1 - clamp((clientY - rect.top) / rect.height, 0, 1)) * 100;
    model.value = currentHex.value;
}

let isDragging = false;
let cleanupDrag: (() => void) | null = null;

function startDrag(e: MouseEvent | TouchEvent) {
    e.preventDefault();
    isDragging = true;
    updateFromPointer(e);
    const onMove = (ev: MouseEvent | TouchEvent) => {
        if (isDragging) updateFromPointer(ev);
    };
    const onUp = () => {
        isDragging = false;
        window.removeEventListener('mousemove', onMove);
        window.removeEventListener('mouseup', onUp);
        window.removeEventListener('touchmove', onMove as EventListener);
        window.removeEventListener('touchend', onUp);
        cleanupDrag = null;
    };
    window.addEventListener('mousemove', onMove);
    window.addEventListener('mouseup', onUp);
    window.addEventListener('touchmove', onMove as EventListener, {
        passive: false,
    });
    window.addEventListener('touchend', onUp);
    cleanupDrag = onUp;
}

onUnmounted(() => {
    isDragging = false;
    cleanupDrag?.();
});

function onHueInput(e: Event) {
    hue.value = Number((e.target as HTMLInputElement).value);
    model.value = currentHex.value;
}

const hasEyeDropper = typeof window !== 'undefined' && 'EyeDropper' in window;
const isEyeDropperOpen = ref(false);

async function pickFromScreen() {
    try {
        isEyeDropperOpen.value = true;

        // @ts-expect-error — EyeDropper is not yet in TS DOM lib
        const dropper = new window.EyeDropper();

        const { sRGBHex } = await dropper.open();
        initFromHex(sRGBHex);
        model.value = sRGBHex;
    } catch {
        // cancelled
    } finally {
        isEyeDropperOpen.value = false;
    }
}

function onRgbChange(channel: 'r' | 'g' | 'b', val: number | undefined) {
    if (val === undefined) return;
    val = clamp(val, 0, 255);
    const rgb = { ...currentRgb.value, [channel]: val };
    const hsv = rgbToHsv(rgb.r, rgb.g, rgb.b);
    if (hsv.s > 0) hue.value = hsv.h;
    saturation.value = hsv.s;
    brightness.value = hsv.v;
    model.value = currentHex.value;
}

function onHexChange(e: Event) {
    const val = (e.target as HTMLInputElement).value.replace(
        /[^0-9a-fA-F]/g,
        '',
    );
    hexInput.value = val.toUpperCase();
    if (val.length === 6) {
        initFromHex('#' + val);
        model.value = '#' + val.toLowerCase();
    }
}

function selectTailwind(hex: string) {
    model.value = hex;
    isOpen.value = false;
}
</script>

<template>
    <Popover v-model:open="isOpen">
        <PopoverTrigger as-child>
            <slot />
        </PopoverTrigger>
        <PopoverContent
            class="w-85 overflow-hidden p-0"
            :side-offset="6"
            :class="{'opacity-0': isEyeDropperOpen}"
            align="start"
        >
            <!-- Tab bar -->
            <div class="border-border flex border-b">
                <button
                    class="flex flex-1 items-center justify-center gap-1.5 px-3 py-3 text-xs font-medium transition-colors bg-card"
                    :class="
                        activeTab === 'custom'
                            ? 'text-foreground -mb-px border-r bg-card'
                            : 'text-muted-foreground hover:text-foreground bg-muted'
                    "
                    @click="activeTab = 'custom'"
                >
                    <IconPalette class="size-5" />
                    {{ $t('Color Picker') }}
                </button>
                <button
                    class="flex flex-1 items-center justify-center gap-1.5 px-3 py-3 text-xs font-medium transition-colors"
                    :class="
                        activeTab === 'tailwind'
                            ? 'text-foreground -mb-px border-l bg-card'
                            : 'text-muted-foreground hover:text-foreground bg-muted'
                    "
                    @click="activeTab = 'tailwind'"
                >
                    <IconTailwind class="size-5 text-sky-400" />
                    {{ $t('Tailwind Colors') }}
                </button>
            </div>

            <!-- Custom Color tab -->
            <template v-if="activeTab === 'custom'">
                <!-- Gradient picker area -->
                <div
                    ref="gradientRef"
                    class="relative h-40 w-full cursor-crosshair select-none"
                    :style="{ background: hueHsl }"
                    @mousedown="startDrag"
                    @touchstart.prevent="startDrag"
                >
                    <div
                        class="pointer-events-none absolute inset-0"
                        style="
                            background: linear-gradient(
                                to right,
                                white,
                                transparent
                            );
                        "
                    />
                    <div
                        class="pointer-events-none absolute inset-0"
                        style="
                            background: linear-gradient(
                                to bottom,
                                transparent,
                                black
                            );
                        "
                    />
                    <div
                        class="pointer-events-none absolute size-4 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white shadow-sm ring-1 ring-black/30"
                        :style="{
                            left: `${saturation}%`,
                            top: `${100 - brightness}%`,
                            background: currentHex,
                        }"
                    />
                </div>

                <!-- Controls -->
                <div class="space-y-2.5 p-2.5">
                    <div class="flex items-center gap-2">
                        <button
                            v-if="hasEyeDropper"
                            class="text-muted-foreground hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring bg-muted shrink-0 rounded-md p-1.5 transition-colors focus-visible:ring-2 focus-visible:outline-none"
                            :title="$t('Pick color from screen')"
                            @click="pickFromScreen"
                        >
                            <IconEyedropper class="size-4" />
                        </button>
                        <div
                            class="border-border size-9 shrink-0 rounded-full border-2 shadow-sm"
                            :style="{ background: currentHex }"
                        />
                        <input
                            type="range"
                            min="0"
                            max="360"
                            :value="hue"
                            class="color-picker-hue-slider flex-1"
                            @input="onHueInput"
                        />
                    </div>

                    <div v-if="mode === 'rgb'" class="flex items-end gap-1.5">
                        <div
                            v-for="ch in ['r', 'g', 'b'] as const"
                            :key="ch"
                            class="min-w-0 flex-1"
                        >
                            <NumberField
                                :model-value="currentRgb[ch]"
                                :min="0"
                                :max="255"
                                :step="1"
                                @update:model-value="(v) => onRgbChange(ch, v)"
                            >
                                <NumberFieldContent>
                                    <NumberFieldDecrement />
                                    <NumberFieldInput
                                        class="dark:bg-input/30 focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                                    />
                                    <NumberFieldIncrement />
                                </NumberFieldContent>
                            </NumberField>
                            <p
                                class="text-muted-foreground mt-1 text-center text-[10px] tracking-widest uppercase"
                            >
                                {{ ch }}
                            </p>
                        </div>
                        <button
                            class="border-border text-muted-foreground hover:bg-accent hover:text-accent-foreground mb-5 shrink-0 rounded-md border p-1.5 transition-colors"
                            :title="$t('Switch to hex input')"
                            @click="mode = 'hex'"
                        >
                            <IconChevronUpDown class="size-4" />
                        </button>
                    </div>

                    <div v-else class="flex items-end gap-1.5">
                        <div class="min-w-0 flex-1">
                            <div
                                class="border-input bg-background focus-within:border-ring focus-within:ring-ring/50 dark:bg-input/30 flex overflow-hidden rounded-md border shadow-xs transition-[color,box-shadow] focus-within:ring-[3px]"
                            >
                                <span
                                    class="text-muted-foreground flex items-center pl-3 text-sm"
                                    >#</span
                                >
                                <Input
                                    type="text"
                                    maxlength="6"
                                    :model-value="hexInput"
                                    class="text-foreground rounded-none border-0 bg-transparent uppercase tabular-nums shadow-none focus-visible:ring-0 dark:bg-transparent"
                                    @input="onHexChange"
                                />
                            </div>
                            <p
                                class="text-muted-foreground mt-1 text-center text-[10px] tracking-widest uppercase"
                            >
                                Hex
                            </p>
                        </div>
                        <button
                            class="border-border text-muted-foreground hover:bg-accent hover:text-accent-foreground mb-5 shrink-0 rounded-md border p-1.5 transition-colors"
                            :title="$t('Switch to RGB input')"
                            @click="mode = 'rgb'"
                        >
                            <IconChevronUpDown class="size-4" />
                        </button>
                    </div>
                </div>
            </template>

            <!-- Tailwind Colors tab -->
            <TailwindColorPicker v-else @select="selectTailwind" />
        </PopoverContent>
    </Popover>
</template>

<style scoped>
.color-picker-hue-slider {
    -webkit-appearance: none;
    appearance: none;
    height: 12px;
    border-radius: 9999px;
    background: linear-gradient(
        to right,
        hsl(0, 100%, 50%),
        hsl(60, 100%, 50%),
        hsl(120, 100%, 50%),
        hsl(180, 100%, 50%),
        hsl(240, 100%, 50%),
        hsl(300, 100%, 50%),
        hsl(360, 100%, 50%)
    );
    cursor: pointer;
    outline: none;
}

.color-picker-hue-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: white;
    border: 2px solid rgba(0, 0, 0, 0.2);
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
    cursor: pointer;
}

.color-picker-hue-slider::-moz-range-thumb {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: white;
    border: 2px solid rgba(0, 0, 0, 0.2);
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
    cursor: pointer;
}
</style>
