<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import IconHeart from '~icons/heroicons/heart';
</script>

<template>
    <footer class="mt-auto w-full">
        <div class="mx-auto max-w-7xl px-6 py-8">
            <div
                class="flex flex-col items-center justify-between gap-4 text-sm text-gray-600 md:flex-row dark:text-gray-400"
            >
                <div class="flex items-center gap-6">
                    <span>© {{ new Date().getFullYear() }} Saucebase</span>
                    <a
                        href="https://github.com/saucebase-dev/saucebase"
                        class="hover:text-gray-900 dark:hover:text-white"
                    >
                        {{ $t('GitHub') }}
                    </a>
                    <a
                        href="https://saucebase-dev.github.io/docs/"
                        class="hover:text-gray-900 dark:hover:text-white"
                    >
                        {{ $t('Documentation') }}
                    </a>
                    <Link
                        :href="route('privacy')"
                        class="hover:text-gray-900 dark:hover:text-white"
                    >
                        {{ $t('Privacy') }}
                    </Link>
                    <Link
                        :href="route('terms')"
                        class="hover:text-gray-900 dark:hover:text-white"
                    >
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
    </footer>
</template>
