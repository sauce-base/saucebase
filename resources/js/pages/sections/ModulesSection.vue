<script setup lang="ts">
import {
    Check,
    CreditCard,
    ExternalLink,
    Lock,
    Map,
    Megaphone,
    Newspaper,
    Settings2,
    Users,
} from 'lucide-vue-next';
import { type Component } from 'vue';

const iconMap: Record<string, Component> = {
    'lucide:lock': Lock,
    'lucide:settings-2': Settings2,
    'lucide:credit-card': CreditCard,
    'lucide:map': Map,
    'lucide:megaphone': Megaphone,
    'lucide:newspaper': Newspaper,
    'lucide:users': Users,
};

const modules = [
    {
        id: 'auth',
        title: 'Auth',
        description:
            'Complete authentication system with login, registration, magic link (passwordless), password reset, email verification, and OAuth integration (Google, GitHub).',
        color: '#7c3aed',
        logo: 'lucide:lock',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/auth',
        features: [
            'Login & Register',
            'Email Verification',
            'OAuth',
            'Security',
            'Magic Link',
        ],
    },
    {
        id: 'settings',
        title: 'Settings',
        description:
            'Flexible settings management for user preferences and system-wide configuration with validation and caching.',
        color: '#0ea5e9',
        logo: 'lucide:settings-2',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/settings',
        features: ['User Preferences', 'System Config', 'Caching'],
    },
    {
        id: 'billing',
        title: 'Billing',
        description:
            'Subscription management and payment processing via Stripe with checkout sessions, billing portal, invoices, and webhook processing.',
        color: '#16a34a',
        logo: 'lucide:credit-card',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/billing',
        features: ['Checkout', 'Billing Portal', 'Invoices', 'Webhooks'],
    },
    {
        id: 'roadmap',
        title: 'Roadmap',
        description:
            'Public roadmap with feature requests, voting, moderation, six statuses, and a Filament admin panel.',
        color: '#f59e0b',
        logo: 'lucide:map',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/roadmap',
        features: ['Feature Requests', 'Voting', 'Admin Panel'],
    },
    {
        id: 'announcements',
        title: 'Announcements',
        description:
            'Site-wide announcement banners with scheduling, audience targeting, and cookie-based dismissal, managed from the Filament admin panel.',
        color: '#6366f1',
        logo: 'lucide:megaphone',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/announcements',
        features: [
            'Banner',
            'Scheduling',
            'Audience Targeting',
            'Dismissal',
            'Admin Panel',
        ],
    },
    {
        id: 'blog',
        title: 'Blog',
        description:
            'Full-featured blog with posts, categories, tags, and a Filament admin panel for content management.',
        color: '#ec4899',
        logo: 'lucide:newspaper',
        badge: 'coming-soon',
        href: null,
        features: [
            'Posts',
            'Categories & Tags',
            'Admin Panel',
            'SEO Optimized',
        ],
    },
    {
        id: 'teams',
        title: 'Teams',
        description:
            'Multi-user team collaboration with role-based permissions, invitations, and team switching for B2B SaaS applications.',
        color: '#6b7280',
        logo: 'lucide:users',
        badge: 'coming-soon',
        href: null,
        features: ['Team Collaboration', 'Role Permissions', 'Invitations'],
    },
];
</script>

<template>
    <section
        id="modules"
        class="border-border bg-muted/30 border-t py-24 sm:py-32"
    >
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <!-- Header -->
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-primary text-2xl font-semibold">
                    {{ $t('Ready-made modules') }}
                </h2>
                <p
                    class="text-foreground mt-2 text-4xl font-semibold tracking-tight text-pretty sm:text-5xl"
                >
                    {{ $t('Install what you need.') }}
                </p>
                <p class="text-muted-foreground mt-6 text-lg/8">
                    {{
                        $t(
                            'Each module copies directly into your repository. Modify models, migrations, controllers, or components, no packages to fork, no APIs to override.',
                        )
                    }}
                </p>
            </div>

            <!-- Modules Grid -->
            <div
                class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-6 sm:grid-cols-2 lg:mx-0 lg:max-w-none lg:grid-cols-3 xl:grid-cols-4"
            >
                <div
                    v-for="mod in modules"
                    :key="mod.id"
                    class="group bg-card relative flex flex-col rounded-xl border p-6 transition-all duration-200 hover:shadow-lg/90"
                    :class="
                        mod.badge === 'coming-soon'
                            ? 'border-border border-dashed opacity-60'
                            : 'border-border hover:border-border/70'
                    "
                >
                    <!-- Icon + Badge -->
                    <div class="flex items-center justify-between">
                        <div
                            class="flex size-10 items-center justify-center rounded-xl"
                            :style="{
                                backgroundColor: mod.color + '20',
                                color: mod.color,
                            }"
                        >
                            <component
                                :is="iconMap[mod.logo]"
                                class="size-5"
                                aria-hidden="true"
                            />
                        </div>
                        <span
                            v-if="mod.badge === 'free'"
                            class="inline-flex items-center rounded-xl bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 ring-1 ring-green-600/20 ring-inset dark:bg-green-400/10 dark:text-green-400 dark:ring-green-400/20"
                        >
                            {{ $t('Free') }}
                        </span>
                        <span
                            v-else
                            class="bg-muted text-muted-foreground ring-border/20 inline-flex items-center rounded-xl px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                        >
                            {{ $t('Coming soon') }}
                        </span>
                    </div>

                    <!-- Name + Description -->
                    <h3 class="text-foreground mt-5 text-base font-semibold">
                        {{ $t(mod.title) }}
                    </h3>
                    <p class="text-muted-foreground mt-1.5 text-sm leading-6">
                        {{ $t(mod.description) }}
                    </p>

                    <!-- Feature list -->
                    <ul class="mt-5 grow space-y-1.5">
                        <li
                            v-for="feature in mod.features"
                            :key="feature"
                            class="text-foreground flex items-center gap-2 text-sm font-medium"
                        >
                            <Check
                                class="size-3.5 shrink-0"
                                :style="{ color: mod.color }"
                                aria-hidden="true"
                            />
                            {{ $t(feature) }}
                        </li>
                    </ul>

                    <!-- Docs link -->
                    <a
                        v-if="mod.href"
                        :href="mod.href"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-6 inline-flex items-center gap-1.5 text-sm font-medium transition-colors"
                        :style="{ color: mod.color }"
                    >
                        {{ $t('View docs') }}
                        <ExternalLink class="size-3.5" aria-hidden="true" />
                    </a>
                </div>
            </div>

            <!-- Build your own -->
            <div class="mt-16 text-center">
                <p class="text-foreground text-2xl font-bold">
                    {{ $t('Build your own module:') }}
                </p>
                <div
                    class="mt-5 inline-flex items-center gap-3 rounded-xl bg-gray-950 px-6 py-4 dark:bg-gray-900"
                >
                    <span class="text-gray-500 select-none">$</span>
                    <code class="text-base text-green-400"
                        >php artisan saucebase:recipe ModuleName</code
                    >
                </div>
            </div>
        </div>
    </section>
</template>
