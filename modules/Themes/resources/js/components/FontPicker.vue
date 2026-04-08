<script setup lang="ts">
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import { trans } from 'laravel-vue-i18n';
import { computed, ref } from 'vue';
import type { Font } from '../types';
import SearchInput from './SearchInput.vue';

const props = defineProps<{
    fonts: Font[];
    testId?: string;
}>();

const model = defineModel<string | null>({ default: null });

const isOpen = ref(false);
const search = ref('');

// If the current value isn't in the provided list, prepend a synthetic entry so it
// shows as selected rather than falling back to "Default".
const allFonts = computed<Font[]>(() => {
    if (!model.value || props.fonts.some((f) => f.family === model.value)) {
        return props.fonts;
    }
    return [
        { family: model.value, category: 'system', variants: [] },
        ...props.fonts,
    ];
});

const currentFont = computed(
    () => allFonts.value.find((f) => f.family === model.value) ?? null,
);
const currentLabel = computed(
    () => currentFont.value?.family ?? model.value ?? trans('Default'),
);
const currentFontCategory = computed(() =>
    currentFont.value ? `font-${currentFont.value?.category}` : '',
);

const filtered = computed(() => {
    const q = search.value.toLowerCase().trim();
    if (!q) {
        return allFonts.value;
    }
    return allFonts.value.filter((f) => f.family.toLowerCase().includes(q));
});

function select(font: Font) {
    model.value = font.family;
}
</script>

<template>
    <Popover v-model:open="isOpen">
        <!-- Trigger -->
        <PopoverTrigger as-child>
            <button
                :data-testid="props.testId"
                class="border-border bg-input hover:bg-input/70 hover:text-input focus-visible:ring-ring flex w-full items-center gap-3 rounded-lg border px-3 py-2.5 text-sm shadow-sm transition-colors focus-visible:ring-2 focus-visible:outline-none"
                :class="currentFontCategory"
            >
                <span
                    class="bg-background text-foreground flex size-7 shrink-0 items-center justify-center rounded-md text-xs font-semibold shadow-sm"
                >
                    Aa
                </span>
                <span class="text-foreground flex-1 text-left font-medium">{{
                    currentLabel
                }}</span>
                <svg
                    class="text-muted-foreground size-4 shrink-0 transition-transform duration-200"
                    :class="{ 'rotate-180': isOpen }"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="m6 9 6 6 6-6" />
                </svg>
            </button>
        </PopoverTrigger>

        <!-- Dropdown -->
        <PopoverContent
            class="overflow-hidden p-0"
            :side-offset="4"
            align="start"
            :style="{ width: 'var(--reka-popper-anchor-width)' }"
            :class="currentFontCategory"
        >
            <!-- Search -->
            <div class="border-border border-b px-3 py-2">
                <SearchInput
                    v-model="search"
                    :placeholder="$t('Search fonts...')"
                />
            </div>

            <!-- Font list -->
            <ScrollArea class="h-60">
                <div class="p-1">
                    <button
                        v-for="font in filtered"
                        :key="font.family"
                        :data-testid="`font-option-${font.family.toLowerCase().replace(/\s+/g, '-')}`"
                        class="hover:bg-accent focus-visible:ring-ring flex w-full items-center gap-3 rounded-md px-2 py-1 text-left transition-colors focus-visible:ring-2 focus-visible:outline-none"
                        :class="{ 'bg-accent/50': font.family === model }"
                        @click="select(font)"
                    >
                        <span
                            class="bg-background text-foreground flex size-7 shrink-0 items-center justify-center rounded-md text-xs font-semibold shadow-sm"
                        >
                            Aa
                        </span>
                        <div class="flex-1 overflow-hidden">
                            <p
                                class="text-foreground truncate text-sm font-medium"
                            >
                                {{ font.family }}
                            </p>
                            <p class="text-muted-foreground text-xs">
                                {{ font.category
                                }}<template v-if="font.variable">
                                    · Variable</template
                                >
                            </p>
                        </div>
                        <svg
                            v-if="font.family === model"
                            class="text-muted-foreground size-4 shrink-0"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                    </button>

                    <!-- Empty state -->
                    <div
                        v-if="filtered.length === 0"
                        class="flex flex-col items-center gap-2 py-10 text-center"
                    >
                        <svg
                            class="text-muted-foreground/40 size-8"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                        <p class="text-muted-foreground text-sm">
                            {{ $t('No fonts found') }}
                        </p>
                    </div>
                </div>
            </ScrollArea>
        </PopoverContent>
    </Popover>
</template>
