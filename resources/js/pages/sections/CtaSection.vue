<script setup lang="ts">
import { modules } from '@/composables/useModules';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const hasAuthModule = computed(() => modules().has('auth'));
</script>

<template>
    <div
        class="relative isolate overflow-hidden border-t border-gray-200 bg-white py-24 sm:py-32 dark:border-gray-800 dark:bg-gray-900"
    >
        <!-- Background gradient -->
        <div
            class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,var(--color-indigo-100),white)] opacity-10 dark:bg-[radial-gradient(45rem_50rem_at_top,var(--color-indigo-500),transparent)] dark:opacity-30"
        />

        <div class="mx-auto max-w-3xl px-6 text-center lg:px-8">
            <h2
                class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl dark:text-white"
            >
                {{ $t('The foundation is built.') }}
                <span class="text-primary block">{{
                    $t('Focus on your product.')
                }}</span>
            </h2>
            <p
                class="mx-auto mt-6 max-w-xl text-lg/8 text-gray-600 dark:text-gray-400"
            >
                {{
                    $t(
                        'Authentication, billing, admin panel, and a modular architecture, all yours from day one. Clone the repo and start building what matters.',
                    )
                }}
            </p>

            <div
                class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row"
            >
                <Link
                    v-if="hasAuthModule && !user"
                    :href="route('register')"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary inline-flex items-center justify-center rounded-full px-8 py-4 text-lg font-semibold transition-all duration-200 hover:scale-105 focus:ring-2 focus:ring-offset-2 focus:outline-hidden dark:focus:ring-offset-gray-950"
                >
                    {{ $t('Get Started Free') }}
                </Link>
                <a
                    href="https://saucebase-dev.github.io/docs/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="focus:ring-primary inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-8 py-4 text-lg font-semibold text-gray-700 transition-all duration-200 hover:scale-105 hover:bg-gray-50 focus:ring-2 focus:ring-offset-2 focus:outline-hidden dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-950"
                >
                    {{ $t('Read the Docs') }}
                </a>
            </div>

            <p class="mt-6">
                <a
                    href="https://github.com/saucebase-dev/saucebase"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    {{ $t('View on GitHub') }} &rarr;
                </a>
            </p>
        </div>
    </div>
</template>
