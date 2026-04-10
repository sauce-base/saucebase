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
        class="border-border bg-background relative isolate overflow-hidden border-t py-24 sm:py-32"
    >
        <!-- Background gradient -->
        <div
            class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,var(--color-primary),transparent)] opacity-10"
        />

        <div class="mx-auto max-w-3xl px-6 text-center lg:px-8">
            <h2
                class="text-foreground text-4xl font-bold tracking-tight sm:text-5xl"
            >
                {{ $t('The foundation is built.') }}
                <span class="text-primary block">{{
                    $t('Focus on your product.')
                }}</span>
            </h2>
            <p class="text-muted-foreground mx-auto mt-6 max-w-xl text-lg/8">
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
                    class="bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary focus:ring-offset-background inline-flex items-center justify-center rounded-xl px-8 py-4 text-lg font-semibold transition-all duration-200  focus:ring-2 focus:ring-offset-2 focus:outline-hidden"
                >
                    {{ $t('Get Started Free') }}
                </Link>
                <a
                    href="https://saucebase-dev.github.io/docs/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="focus:ring-primary border-border bg-background text-foreground hover:bg-accent focus:ring-offset-background inline-flex items-center justify-center rounded-xl border px-8 py-4 text-lg font-semibold transition-all duration-200  focus:ring-2 focus:ring-offset-2 focus:outline-hidden"
                >
                    {{ $t('Read the Docs') }}
                </a>
            </div>

            <p class="mt-6">
                <a
                    href="https://github.com/saucebase-dev/saucebase"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-muted-foreground hover:text-foreground text-sm"
                >
                    {{ $t('View on GitHub') }} &rarr;
                </a>
            </p>
        </div>
    </div>
</template>
