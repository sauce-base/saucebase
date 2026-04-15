<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { BookOpen, Check, Copy, Terminal, X } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';
import { toast } from 'vue-sonner';
import type { Module } from './index';

const props = defineProps<{ selectedMod: Module | null }>();
const emit = defineEmits<{ close: [] }>();

const copied = ref(false);
let copyTimer: ReturnType<typeof setTimeout> | null = null;

function installCommand(mod: Module) {
    if (mod.id === 'custom')
        return 'php artisan saucebase:recipe MyAmazingModuleIdea';
    return `composer require saucebase/${mod.id}`;
}

function copyCommand() {
    if (!props.selectedMod) return;
    navigator.clipboard
        .writeText(installCommand(props.selectedMod))
        .then(() => {
            toast.success(trans('Copied to clipboard'));
            if (copyTimer) clearTimeout(copyTimer);
            copied.value = true;
            copyTimer = setTimeout(() => (copied.value = false), 2000);
        })
        .catch(() => toast.error(trans('Failed to copy')));
}

onUnmounted(() => {
    if (copyTimer) clearTimeout(copyTimer);
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-150"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div
                v-if="selectedMod"
                class="bg-background/50 fixed inset-0 z-50 backdrop-blur-md"
                @click="emit('close')"
            />
        </Transition>

        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            leave-active-class="transition-all duration-200 ease-in"
            enter-from-class="opacity-0 translate-y-6 scale-[0.96]"
            leave-to-class="opacity-0 translate-y-4 scale-[0.97]"
        >
            <div
                v-if="selectedMod"
                class="pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-6 shadow-lg"
            >
                <div
                    class="pointer-events-auto relative w-full max-w-xl"
                    @click.stop
                >
                    <!-- Modal card body -->
                    <div
                        class="bg-card/90 border-border relative z-10 flex flex-col gap-3 rounded-xl border p-6 shadow-[0px_5px_0_0_color-mix(in_oklch,var(--color-white)_85%,black)] dark:shadow-[0px_5px_0_0_color-mix(in_oklch,var(--color-white)_20%,black)]"
                        :style="{ '--mod-color': `var(${selectedMod.color})` }"
                    >
                        <!-- Close button -->
                        <button
                            class="text-muted-foreground hover:text-foreground hover:border-foreground absolute top-3 right-3 z-20 cursor-pointer rounded-full border p-1.5 transition-colors"
                            @click="emit('close')"
                        >
                            <X class="size-4" />
                        </button>

                        <slot name="before" />

                        <!-- Header: icon + title -->
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-full"
                                :style="{
                                    background: `var(${selectedMod.color})`,
                                }"
                            >
                                <component
                                    :is="selectedMod.icon"
                                    class="size-5 text-white"
                                    aria-hidden="true"
                                />
                            </div>
                            <h2
                                class="text-foreground flex-1 text-xl leading-tight font-bold"
                            >
                                {{ selectedMod.title() }}
                            </h2>
                        </div>

                        <!-- Description -->
                        <p class="text-muted-foreground py-2 leading-relaxed">
                            {{ selectedMod.description() }}
                        </p>

                        <!-- Features checklist -->
                        <ul
                            class="grid grid-cols-2 gap-x-4 gap-y-1.5 rounded-sm border p-4"
                        >
                            <li
                                v-for="feature in selectedMod.features"
                                :key="feature()"
                                class="text-foreground flex items-center gap-2 text-sm"
                            >
                                <Check
                                    class="size-3.5 shrink-0"
                                    style="color: var(--mod-color)"
                                    aria-hidden="true"
                                />
                                {{ feature() }}
                            </li>
                        </ul>

                        <!-- Install command (free + custom only) -->
                        <div
                            v-if="selectedMod.href !== null"
                            class="mt-2 flex flex-col gap-2"
                        >
                            <div
                                class="flex items-center gap-3 rounded-full bg-gray-950 px-4 py-3 dark:bg-gray-900 shadow-sm"
                            >
                                <Terminal
                                    class="size-4 shrink-0 text-gray-500"
                                    aria-hidden="true"
                                />
                                <code class="flex-1 text-sm text-green-400">
                                    {{ installCommand(selectedMod) }}
                                </code>
                                <button
                                    class="cursor-pointer text-gray-300 transition-colors hover:text-gray-300"
                                    @click="copyCommand"
                                >
                                    <Check
                                        v-if="copied"
                                        class="size-4 text-green-400"
                                    />
                                    <Copy v-else class="size-4" />
                                </button>
                            </div>
                            <p
                                v-if="selectedMod.id !== 'custom'"
                                class="text-muted-foreground pb-2 text-center text-sm"
                            >
                                {{
                                    $t(
                                        'This module may require additional steps after installation, check the docs',
                                    )
                                }}
                            </p>
                        </div>

                        <!-- CTA -->
                        <div class="-mx-6 border-t px-6 pt-4">
                            <a
                                v-if="selectedMod.href !== null"
                                :href="selectedMod.href"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex w-full items-center justify-center gap-2 rounded-full px-6 py-2.5 text-sm font-semibold text-white shadow-[0_5px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_7px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)]"
                                :style="{
                                    background: `var(${selectedMod.color})`,
                                }"
                            >
                                <BookOpen class="size-4" aria-hidden="true" />
                                {{ $t('Read the Documentation') }}
                            </a>
                            <span
                                v-else
                                class="bg-muted/90 border-muted-foreground/20 text-muted-foreground flex w-full items-center justify-center gap-2 rounded-full border px-6 py-2.5 text-sm font-semibold"
                            >
                                {{ $t('Coming Soon') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
