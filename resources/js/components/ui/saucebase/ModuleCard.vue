<script setup lang="ts">
import type { Module } from './index';

const props = defineProps<{ module: Module; index: number, moduleClass?: string }>();
const emit = defineEmits<{ select: [module: Module] }>();
</script>

<template>
    <div
        data-card
        class="select-none relative cursor-pointer transition-opacity duration-200 hover:opacity-100!"
        :class="[!module.href ? 'opacity-50' : '', moduleClass]"
        :style="{
            '--mod-color': `var(${module.color})`,
            '--card-delay': `${400 + index * 150}ms`,
        }"
        @click="emit('select', module)"
    >
        <!-- Diagonal stripe accent — stationary, fades in after card lands -->
        <div
            class="stripe stripe-appear absolute inset-x-2 top-9 bottom-0 w-full -translate-x-5 translate-y-2.5 rounded-xl transition-opacity duration-200"
            :class="
                module.href
                    ? 'opacity-90 group-hover/card:opacity-90'
                    : 'opacity-80'
            "
            :style="{
                animationDelay: 'calc(var(--card-delay) + 50ms)',
            }"
        />

        <!-- Animated card (falls in) -->
        <div
            class="card-drop group/card relative flex flex-col pt-6"
            :style="{ animationDelay: 'var(--card-delay)' }"
        >
            <div class="relative flex-1">
                <!-- Card body -->
                <div
                    class="bg-card relative z-10 flex h-full flex-col gap-2 rounded-xl px-4 pt-12 pb-6 text-left shadow-[-1px_1px_0_0_color-mix(in_oklch,var(--color-white)_80%,black)] transition-all duration-200 group-hover/card:translate-x-1.5 group-hover/card:-translate-y-1.5 group-hover/card:shadow-[-5px_5px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)]/70 dark:shadow-[-2px_2px_0_0_color-mix(in_oklch,var(--color-muted)_90%,black)] group-hover/card:dark:shadow-[-5px_5px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)]/70"
                    :class="!module.href ? 'border-dashed' : ''"
                >
                    <!-- Badge (stripe background + chip) -->
                    <template v-if="module.badge">
                        <div
                            class="badge-stripe stripe absolute top-3 right-0.5 min-w-10 rounded-full px-1.5 py-0.5 text-[9px] font-bold opacity-50"
                            aria-hidden="true"
                        >
                            <span class="invisible">
                                {{ module.badge.label() }}
                            </span>
                        </div>
                        <div
                            class="bounce absolute top-1.5 -right-1.5 z-10 transition-all group-hover/card:translate-x-1 group-hover/card:-translate-y-0.5"
                            :style="{
                                animationDelay:
                                    'calc(var(--card-delay) + 280ms)',
                            }"
                        >
                            <div
                                class="flex min-w-10 items-center justify-center rounded-full border px-1.5 py-0.5 text-[9px] font-bold shadow-sm"
                                :class="module.badge.class"
                            >
                                {{ module.badge.label() }}
                            </div>
                        </div>
                    </template>

                    <!-- Floating icon -->
                    <div
                        class="bounce absolute -top-2 left-1/2 z-10 -ml-5 flex size-14 shrink-0 items-center justify-center rounded-full shadow-[-2px_2px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)] transition-all duration-200 group-hover/card:translate-x-1.5 group-hover/card:-translate-y-1.5 group-hover/card:shadow-[-5px_5px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)]"
                        :style="{
                            background: `var(${module.color})`,
                            animationDelay: 'calc(var(--card-delay) + 280ms)',
                        }"
                    >
                        <component
                            :is="module.icon"
                            class="absolute size-7 text-black/10 transition-all duration-200 group-hover/card:text-black/30 group-hover/card:blur-[1.5px]"
                            aria-hidden="true"
                        />
                        <component
                            :is="module.icon"
                            class="bounce absolute size-7 translate-x-0.5 -translate-y-0.5 text-white transition-transform duration-200 group-hover/card:translate-x-1 group-hover/card:-translate-y-1.5"
                            :style="{
                                animationDelay:
                                    'calc(var(--card-delay) + 280ms)',
                            }"
                            aria-hidden="true"
                        />
                    </div>

                    <!-- Stripe ring around icon -->
                    <div
                        class="stripe absolute top-0 left-1/2 -ml-7 size-14 rounded-full"
                    />

                    <span
                        class="text-foreground mt-6 text-center text-base leading-tight font-semibold"
                    >
                        {{ module.title() }}
                    </span>
                    <p
                        class="text-muted-foreground line-clamp-3 text-center text-xs leading-snug"
                    >
                        {{ module.description() }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
