<script setup lang="ts">
import { ScrollArea } from '@/components/ui/scroll-area';
import colors from 'tailwindcss/colors';
import { computed, ref } from 'vue';
import IconList from '~icons/heroicons/bars-3';
import IconSearch from '~icons/heroicons/magnifying-glass';
import IconGrid from '~icons/heroicons/squares-2x2';
import SearchInput from './SearchInput.vue';

const shades = [
    '50',
    '100',
    '200',
    '300',
    '400',
    '500',
    '600',
    '700',
    '800',
    '900',
    '950',
] as const;
const IGNORE = new Set(['current', 'inherit']);

const tailwindColors = Object.entries(colors)
    .filter(([key]) => !IGNORE.has(key))
    .map(([key, value]) => {
        const name = key.charAt(0).toUpperCase() + key.slice(1);
        if (typeof value === 'string') {
            return { key, name, colors: [{ name: key, value }] };
        }
        const palette = value as Record<string, string>;
        return {
            key,
            name,
            colors: shades
                .filter((shade) => palette[shade])
                .map((shade) => ({
                    name: `${key}-${shade}`,
                    value: palette[shade],
                })),
        };
    })
    .filter((fam) => fam.colors.length > 0);

const emit = defineEmits<{ select: [value: string] }>();

const search = ref('');
const viewMode = ref<'list' | 'grid'>('list');

const filtered = computed(() => {
    const q = search.value.toLowerCase().trim();
    if (!q) {
        return tailwindColors;
    }
    return tailwindColors
        .map((fam) => ({
            ...fam,
            colors: fam.colors.filter(
                (c) => c.name.includes(q) || fam.name.toLowerCase().includes(q),
            ),
        }))
        .filter((fam) => fam.colors.length > 0);
});
</script>

<template>
    <!-- Search + view toggle -->
    <div
        class="border-border bg-card flex items-center gap-2 border-b px-3 py-2"
    >
        <SearchInput
            v-model="search"
            class="flex-1"
            :placeholder="$t('Search Tailwind colors...')"
        />
        <div
            class="border-border flex shrink-0 overflow-hidden rounded-md border"
        >
            <button
                class="cursor-pointer px-2 py-1.5 transition-colors"
                :class="
                    viewMode === 'list'
                        ? 'bg-primary/70 text-primary-foreground'
                        : 'text-muted-foreground hover:bg-primary/10 hover:text-primary'
                "
                :aria-label="$t('List view')"
                @click="viewMode = 'list'"
            >
                <IconList class="size-4" />
            </button>
            <button
                class="cursor-pointer px-2 py-1.5 transition-colors"
                :class="
                    viewMode === 'grid'
                        ? 'bg-primary/70 text-primary-foreground'
                        : 'text-muted-foreground hover:bg-primary/10 hover:text-primary'
                "
                :aria-label="$t('Grid view')"
                @click="viewMode = 'grid'"
            >
                <IconGrid class="size-4" />
            </button>
        </div>
    </div>

    <!-- Color list -->
    <ScrollArea class="h-60">
        <div class="p-2">
            <template v-for="fam in filtered" :key="fam.key">
                <!-- List view -->
                <template v-if="viewMode === 'list'">
                    <button
                        v-for="color in fam.colors"
                        :key="color.name"
                        class="hover:bg-accent focus-visible:ring-ring flex w-full items-center gap-3 rounded-md px-2 py-1.5 text-left transition-colors focus-visible:ring-2 focus-visible:outline-none"
                        @click="emit('select', color.value)"
                    >
                        <span
                            class="border-border/60 size-7 shrink-0 rounded-md border shadow-sm"
                            :style="`background: linear-gradient(${color.value}, ${color.value}), repeating-conic-gradient(#aaa 0% 25%, white 0% 50%) 0 0 / 8px 8px`"
                        />
                        <span class="text-foreground text-sm">
                            {{ color.name }}
                        </span>
                    </button>
                </template>

                <!-- Grid view -->
                <div v-else class="mb-1 grid grid-cols-11 gap-0.5 px-1 py-0.5">
                    <button
                        v-for="color in fam.colors"
                        :key="color.name"
                        class="group focus-visible:outline-none"
                        :title="color.name"
                        @click="emit('select', color.value)"
                    >
                        <span
                            class="border-border/40 group-focus-visible:ring-ring block size-5 rounded border shadow-sm transition-transform group-hover:scale-110 group-focus-visible:ring-2"
                            :style="`background: linear-gradient(${color.value}, ${color.value}), repeating-conic-gradient(#aaa 0% 25%, white 0% 50%) 0 0 / 8px 8px`"
                        />
                    </button>
                </div>
            </template>

            <!-- Empty state -->
            <div
                v-if="filtered.length === 0"
                class="flex flex-col items-center gap-2 py-10 text-center"
            >
                <IconSearch class="text-muted-foreground/40 size-8" />
                <p class="text-muted-foreground text-sm">
                    {{ $t('No colors found') }}
                </p>
            </div>
        </div>
    </ScrollArea>
</template>
