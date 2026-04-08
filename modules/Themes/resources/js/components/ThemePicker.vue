<script setup lang="ts">
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import { useColorMode } from '@vueuse/core';
import { computed, ref } from 'vue';
import type { Theme } from '../types';
import SearchInput from './SearchInput.vue';
import ThemeColorSwatch from './ThemeColorSwatch.vue';

const model = defineModel<string>({ default: 'default' });

const props = defineProps<{
    options: Theme[];
}>();

const { system, store } = useColorMode();

const colorMode = computed(() =>
    store.value === 'auto' ? system.value : store.value,
);

const isOpen = ref(false);
const search = ref('');

const themes = computed(() =>
    props.options.map((p) => ({
        id: p.id,
        name: p.name,
        preview: {
            background: p[colorMode.value]['--background'],
            primary: p[colorMode.value]['--primary'],
            secondary: p[colorMode.value]['--secondary'],
            foreground: p[colorMode.value]['--foreground'],
        },
        radius: p[colorMode.value]['--radius'] ?? '0.625rem',
    })),
);

const currentTheme = computed(
    () => themes.value.find((t) => t.id === model.value) ?? themes.value[0],
);

const filtered = computed(() => {
    const q = search.value.toLowerCase().trim();
    return q
        ? themes.value.filter((t) => t.name.toLowerCase().includes(q))
        : themes.value;
});

function select(id: string) {
    model.value = id;
    isOpen.value = false;
}
</script>

<template>
    <Popover v-model:open="isOpen">
        <!-- Trigger: color circles + theme name + chevron -->
        <PopoverTrigger as-child>
            <button
                data-testid="theme-picker-trigger"
                class="border-border bg-input hover:bg-input/70 hover:text-input focus-visible:ring-ring flex w-full items-center gap-3 rounded-lg border px-3 py-2.5 text-sm shadow-sm transition-colors focus-visible:ring-2 focus-visible:outline-none"
            >
                <ThemeColorSwatch
                    :preview="currentTheme.preview"
                    :radius="currentTheme.radius"
                />
                <span class="text-foreground flex-1 text-left font-medium">
                    {{ currentTheme.name }}
                </span>
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
        >
            <!-- Search -->
            <div class="border-border border-b px-3 py-2">
                <SearchInput
                    v-model="search"
                    :placeholder="$t('Search themes...')"
                    test-id="theme-search"
                />
            </div>

            <!-- Theme list -->
            <ScrollArea class="h-72">
                <div class="p-1">
                    <button
                        v-for="theme in filtered"
                        :key="theme.id"
                        :data-testid="`theme-option-${theme.id}`"
                        class="hover:bg-accent focus-visible:ring-ring flex w-full items-center gap-3 rounded-md px-3 py-2 text-left text-sm transition-colors focus-visible:ring-2 focus-visible:outline-none"
                        @click="select(theme.id)"
                    >
                        <ThemeColorSwatch
                            :preview="theme.preview"
                            :radius="theme.radius"
                        />
                        <span class="text-foreground flex-1 font-medium">
                            {{ theme.name }}
                        </span>
                        <!-- Checkmark -->
                        <svg
                            v-if="theme.id === model"
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
                            {{ $t('No themes found') }}
                        </p>
                    </div>
                </div>
            </ScrollArea>
        </PopoverContent>
    </Popover>
</template>
