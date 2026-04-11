<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import IconHeart from '~icons/heroicons/heart';
</script>

<template>
    <footer class="relative mt-auto w-full overflow-hidden sm:pb-48 pb-12">
        <div
            class="mx-4 py-8 min-[450px]:mx-auto min-[450px]:max-w-7xl min-[450px]:px-6"
        >
            <div
                class="text-muted-foreground flex w-full flex-col items-center justify-between gap-4 text-sm md:flex-row"
            >
                <div
                    class="flex flex-col items-center gap-y-1 md:flex-row md:items-center md:gap-x-6 md:gap-y-0"
                >
                    <span>© {{ new Date().getFullYear() }} Saucebase</span>
                    <a
                        href="https://github.com/saucebase-dev/saucebase"
                        class="hover:text-foreground"
                    >
                        {{ $t('GitHub') }}
                    </a>
                    <a
                        href="https://saucebase-dev.github.io/docs/"
                        class="hover:text-foreground"
                    >
                        {{ $t('Documentation') }}
                    </a>
                    <Link
                        :href="route('privacy')"
                        class="hover:text-foreground"
                    >
                        {{ $t('Privacy') }}
                    </Link>
                    <Link :href="route('terms')" class="hover:text-foreground">
                        {{ $t('Terms') }}
                    </Link>
                </div>
                <div class="flex items-center gap-1">
                    <span>{{ $t('Made with') }}</span>
                    <IconHeart class="h-4 w-4 text-red-500" />
                    <span>{{ $t('for developers') }}</span>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 translate-y-[20%] font-mono scale-105 px-4">
            <p
                class="text-[21vw] leading-none font-black -tracking-widest select-none text-center"
            >
                <span class="text-foreground/5">Sauce</span><span class="text-foreground/10">base</span>
            </p>
        </div>
    </footer>
</template>
