<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Header from '@/components/Header.vue';
import { Head } from '@inertiajs/vue3';
import {
    CreditCard,
    Lightbulb,
    Lock,
    Map,
    Megaphone,
    Newspaper,
    Palette,
    Settings2,
} from 'lucide-vue-next';

const modules = [
    {
        id: 'auth',
        title: 'Auth',
        description:
            'Login, register, OAuth, magic links, and email verification.',
        icon: Lock,
        bg: 'bg-violet-600/20',
        text: 'text-violet-600',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/auth',
    },
    {
        id: 'settings',
        title: 'Settings',
        description: 'User preferences and system-wide configuration.',
        icon: Settings2,
        bg: 'bg-sky-500/20',
        text: 'text-sky-500',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/settings',
    },
    {
        id: 'billing',
        title: 'Billing',
        description:
            'Stripe subscriptions, checkout sessions, and billing portal.',
        icon: CreditCard,
        bg: 'bg-green-600/20',
        text: 'text-green-600',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/billing',
    },
    {
        id: 'roadmap',
        title: 'Roadmap',
        description: 'Public roadmap with feature requests and voting.',
        icon: Map,
        bg: 'bg-amber-500/20',
        text: 'text-amber-500',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/roadmap',
    },
    {
        id: 'announcements',
        title: 'Announcements',
        description: 'Site-wide banners with scheduling and dismissal.',
        icon: Megaphone,
        bg: 'bg-indigo-500/20',
        text: 'text-indigo-500',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/announcements',
    },
    {
        id: 'themes',
        title: 'Themes',
        description:
            'Global visual editor for colors, fonts, radius, and shadows. Bake a theme into CSS without writing code.',
        icon: Palette,
        bg: 'bg-rose-500/20',
        text: 'text-rose-500',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/themes',
    },
    {
        id: 'custom',
        title: 'Your Module',
        description:
            'Build and install your own modules with a single command.',
        icon: Lightbulb,
        bg: 'bg-gray-500/20',
        text: 'text-gray-500',
        badge: 'custom',
        href: 'https://saucebase-dev.github.io/docs/fundamentals/modules',
    },
    {
        id: 'blog',
        title: 'Blog',
        description: 'Posts, categories, tags, and admin panel.',
        icon: Newspaper,
        bg: 'bg-pink-500/20',
        text: 'text-pink-500',
        badge: 'coming-soon',
        href: null,
    },
];
</script>

<template>
    <Head>
        <title>{{ $t('Saucebase — Modular Laravel SaaS Starter Kit') }}</title>
        <meta
            name="description"
            :content="
                $t(
                    'Free, open-source Laravel SaaS starter kit. Ships with auth, billing, admin panel, and a modular copy-and-own architecture.',
                )
            "
        />
    </Head>
    <div class="relative isolate flex min-h-screen flex-col overflow-x-hidden">
        <Header />

        <main class="flex flex-1 flex-col justify-center px-6 py-24 sm:py-28">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div
                    class="mx-auto grid max-w-2xl grid-cols-1 gap-x-16 gap-y-16 sm:gap-y-20 lg:mx-0 lg:max-w-none lg:grid-cols-5"
                >
                    <!-- Left: content (3/5) -->
                    <div class="lg:col-span-3 lg:pt-4">
                        <h1
                            class="text-foreground text-4xl font-bold tracking-tight"
                        >
                            {{ $t("Let's get started.") }}
                        </h1>
                        <p
                            class="text-muted-foreground mt-4 text-base leading-7"
                        >
                            {{
                                $t(
                                    'Your foundation is ready. Focus on what makes your product unique the modules handle the rest.',
                                )
                            }}
                        </p>

                        <!-- Module cards -->
                        <div
                            class="group mt-8 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <component
                                :is="mod.href ? 'a' : 'div'"
                                v-for="mod in modules"
                                :key="mod.id"
                                :href="mod.href ?? undefined"
                                :target="mod.href ? '_blank' : undefined"
                                :rel="
                                    mod.href ? 'noopener noreferrer' : undefined
                                "
                                class="bg-card border-border flex flex-col items-center gap-2 rounded-xl border p-4 text-center transition-all duration-200"
                                :class="
                                    mod.badge === 'coming-soon'
                                        ? 'border-dashed opacity-50 group-hover:opacity-30'
                                        : mod.badge === 'custom'
                                          ? 'cursor-pointer border-dashed group-hover:opacity-50 hover:opacity-100! hover:shadow-md'
                                          : 'cursor-pointer group-hover:opacity-50 hover:opacity-100! hover:shadow-md'
                                "
                            >
                                <div
                                    class="flex size-8 shrink-0 items-center justify-center rounded-lg"
                                    :class="[mod.bg, mod.text]"
                                >
                                    <component
                                        :is="mod.icon"
                                        class="size-4"
                                        aria-hidden="true"
                                    />
                                </div>
                                <span
                                    class="text-foreground text-sm font-medium leading-tight"
                                >
                                    {{ $t(mod.title) }}
                                </span>
                                <p
                                    class="text-muted-foreground line-clamp-2 text-xs leading-snug"
                                >
                                    {{ $t(mod.description) }}
                                </p>
                            </component>
                        </div>

                        <!-- CTA -->
                        <div class="mt-8">
                            <a
                                href="https://saucebase-dev.github.io/docs/"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="bg-foreground text-background hover:bg-foreground/90 flex w-full items-center justify-center rounded-xl px-6 py-3 text-sm font-semibold transition-all md:w-auto md:inline-flex"
                            >
                                {{ $t('Read the Documentation') }}
                            </a>
                        </div>
                    </div>

                    <!-- Right: screenshot (2/5), overflows to edge -->
                    <img
                        src="/images/screenshots/dashboard-dark.png"
                        :alt="$t('Saucebase dashboard screenshot')"
                        class="w-3xl max-w-none rounded-xl shadow-xl ring-1 ring-white/10 not-dark:hidden sm:w-228 lg:col-span-2"
                    />
                    <img
                        src="/images/screenshots/dashboard-light.png"
                        :alt="$t('Saucebase dashboard screenshot')"
                        class="w-3xl max-w-none rounded-xl shadow-2xl ring-1 ring-gray-400/20 sm:w-228 lg:col-span-2 dark:hidden"
                    />
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
