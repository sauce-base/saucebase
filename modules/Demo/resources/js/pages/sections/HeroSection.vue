<script setup lang="ts">
import type { Module } from '@/components/ui/saucebase';
import { ModuleCard, ModuleModal, modules } from '@/components/ui/saucebase';
import { trans } from 'laravel-vue-i18n';
import { BookOpen, Check, Copy, Terminal } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';
import { toast } from 'vue-sonner';

const selectedMod = ref<Module | null>(null);

const copiedInstall = ref(false);
let copyInstallTimer: ReturnType<typeof setTimeout> | null = null;

function copyInstall() {
    navigator.clipboard
        .writeText('laravel new --using=saucebase/saucebase')
        .then(() => {
            toast.success(trans('Copied to clipboard'));
            if (copyInstallTimer) clearTimeout(copyInstallTimer);
            copiedInstall.value = true;
            copyInstallTimer = setTimeout(
                () => (copiedInstall.value = false),
                2000,
            );
        })
        .catch(() => toast.error(trans('Failed to copy')));
}

onUnmounted(() => {
    if (copyInstallTimer) clearTimeout(copyInstallTimer);
});
</script>

<template>
    <div class="bg-background relative isolate flex min-h-screen flex-col">
        <main class="mx-auto w-full px-6 py-16 lg:px-8">
            <div class="py-14">
                <h1
                    class="text-primary text-center text-7xl font-bold [text-shadow:0_4px_25px_color-mix(in_oklch,var(--color-primary)_15%,var(--color-background))]"
                >
                    {{ $t("With Saucebase") }}
                </h1>
                <h2 class="text-secondary mt-1 text-center text-5xl font-bold tracking-tight">
                    {{ $t('Your foundation is ready!') }}
                </h2>
                <p
                    class="text-muted-foreground mt-3 text-center text-3xl tracking-tighter"
                >
                    {{
                        $t(
                            'Your recipe first. Modules for everything else.',
                        )
                    }}
                </p>
            </div>
            <div class="mx-auto gap-4">
                <div class="relative max-w-xl mx-auto -mt-3">
                    <div
                        class="flex items-center gap-3 rounded-full bg-gray-950 px-4 py-3 dark:bg-gray-900 shadow-sm"
                    >
                        <Terminal
                            class="size-5 shrink-0 text-gray-500"
                            aria-hidden="true"
                        />
                        <code class="flex-1 text-green-400">
                            laravel new --using=saucebase/saucebase
                        </code>
                        <button
                            class="cursor-pointer text-gray-300 transition-colors hover:text-white"
                            @click="copyInstall"
                        >
                            <Check
                                v-if="copiedInstall"
                                class="size-5 text-green-400"
                            />
                            <Copy v-else class="size-5" />
                        </button>
                    </div>
                    <div class="mx-auto w-full text-center mt-2 text-muted-foreground/90" >
                        and then you are ready to start your project! 
                    </div>
                </div>
            </div>
            <div
                class="relative overflow-hidden p-18 font-mono"
                style="
                    mask-image:
                        linear-gradient(to bottom, #000 90%, transparent 100%),
                        linear-gradient(to right, #000 90%, transparent 100%),
                        linear-gradient(to top, #000 90%, transparent 100%),
                        linear-gradient(to left, #000 90%, transparent 100%);
                    mask-composite: intersect;
                "
            >
                <!-- Module cards — grid is transformed as one unit for correct alignment -->
                <div
                    class="relative z-10 mx-auto grid max-w-6xl rotate-[-5deg] skew-x-10 grid-cols-1 gap-8 gap-y-2 px-10 pb-16 has-[[data-card]:hover]:*:data-card:opacity-40 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <ModuleCard
                        v-for="(mod, index) in modules"
                        :key="mod.id"
                        :module="mod"
                        :index="index"
                        @select="selectedMod = $event"
                    />
                </div>

                <!-- Light mode pattern -->
                <div
                    class="absolute inset-0 -z-1 -m-5 rotate-[-5deg] skew-x-10 overflow-hidden dark:hidden"
                    style="
                        background-size: 24px;
                        background-position: top left;
                        background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 32 32%22 fill=%22none%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg opacity=%22.4%22 fill=%22%23011E32%22 fill-opacity=%22.24%22%3E%3Cpath fill-rule=%22evenodd%22 clip-rule=%22evenodd%22 d=%22M0 .5V6h.5V.5H6V0H0v.5ZM.5 32H0v-6h.5v5.5H6v.5H.5ZM32 0v6h-.5V.5H26V0h6Zm0 31.5V26h-.5v5.5H26v.5h6v-.5Z%22/%3E%3Cpath opacity=%22.6%22 d=%22M19 0v.5h-6V0zM19 31.5v.5h-6v-.5zM32 19h-.5v-6h.5zM.5 19H0v-6h.5z%22/%3E%3C/g%3E%3C/svg%3E');
                    "
                />
                <!-- Dark mode pattern -->
                <div
                    class="absolute inset-0 -z-1 -m-5 hidden rotate-[-5deg] skew-x-10 overflow-hidden dark:block"
                    style="
                        background-size: 24px;
                        background-position: top left;
                        background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 32 32%22 fill=%22none%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg opacity=%22.5%22 fill=%22%23ffffff%22 fill-opacity=%22.15%22%3E%3Cpath fill-rule=%22evenodd%22 clip-rule=%22evenodd%22 d=%22M0 .5V6h.5V.5H6V0H0v.5ZM.5 32H0v-6h.5v5.5H6v.5H.5ZM32 0v6h-.5V.5H26V0h6Zm0 31.5V26h-.5v5.5H26v.5h6v-.5Z%22/%3E%3Cpath opacity=%22.6%22 d=%22M19 0v.5h-6V0zM19 31.5v.5h-6v-.5zM32 19h-.5v-6h.5zM.5 19H0v-6h.5z%22/%3E%3C/g%3E%3C/svg%3E');
                    "
                />

                <div class="mt-8 flex justify-center">
                    <div class="relative inline-flex">
                        <!-- Stripe layer behind docs button -->
                        <div
                            class="stripe absolute inset-0 translate-y-3 rounded-full"
                            :style="{ '--mod-color': 'var(--foreground)' }"
                        />
                        <a
                            href="https://saucebase-dev.github.io/docs/"
                            class="hover:bg-foreground/80 text-background bg-foreground/90 relative flex items-center gap-2 rounded-full px-8 py-4 text-base font-semibold shadow-[0_5px_0_0_color-mix(in_oklch,var(--color-foreground)_85%,black)] transition-all duration-200 hover:-translate-y-1 hover:shadow-[0_9px_0_0_color-mix(in_oklch,var(--color-foreground)_85%,black)]"
                        >
                            <BookOpen class="size-5" aria-hidden="true" />
                            {{ $t('Read the Documentation') }}
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <ModuleModal :selected-mod="selectedMod" @close="selectedMod = null" />
</template>
