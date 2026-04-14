<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Header from '@/components/Header.vue';
import { Head } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import {
    BarChart3,
    Bell,
    Blocks,
    BookOpen,
    Check,
    Copy,
    CreditCard,
    Lightbulb,
    Lock,
    Map,
    Megaphone,
    Newspaper,
    Palette,
    Settings2,
    Terminal,
    Webhook,
    X,
} from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';
import { toast } from 'vue-sonner';

const BG_GRAY_COLOR = 'bg-gray-500';

const modules = [
    {
        id: 'auth',
        title: 'Auth',
        description:
            'Complete authentication system with login, registration, magic link (passwordless), password reset, email verification, and OAuth integration (Google, GitHub).',
        icon: Lock,
        bg: 'bg-violet-600',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/auth',
        features: [
            'Login & Register',
            'Email Verification',
            'OAuth',
            'Magic Link',
            'Security',
        ],
    },
    {
        id: 'settings',
        title: 'Settings',
        description:
            'Flexible settings management for user preferences and system-wide configuration with validation and caching.',
        icon: Settings2,
        bg: 'bg-sky-500',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/settings',
        features: ['User Preferences', 'System Config', 'Caching'],
    },
    {
        id: 'billing',
        title: 'Billing',
        description:
            'Subscription management and payment processing via Stripe with checkout sessions, billing portal, invoices, and webhook processing.',
        icon: CreditCard,
        bg: 'bg-green-600',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/billing',
        features: ['Checkout', 'Billing Portal', 'Invoices', 'Webhooks'],
    },
    {
        id: 'roadmap',
        title: 'Roadmap',
        description:
            'Public roadmap with feature requests, voting, moderation, six statuses, and a Filament admin panel.',
        icon: Map,
        bg: 'bg-amber-500',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/roadmap',
        features: ['Feature Requests', 'Voting', 'Admin Panel'],
    },
    {
        id: 'announcements',
        title: 'Announcements',
        description:
            'Site-wide announcement banners with scheduling, audience targeting, and cookie-based dismissal, managed from the Filament admin panel.',
        icon: Megaphone,
        bg: 'bg-indigo-500',
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
        id: 'themes',
        title: 'Themes',
        description:
            "Visual theme editor for designing your app's colors, fonts, radius, and shadows. Pick a built-in theme or build your own, then bake it into CSS - no runtime overhead.",
        icon: Palette,
        bg: 'bg-purple-500',
        badge: 'new',
        href: 'https://saucebase-dev.github.io/docs/modules/themes',
        features: [
            '14 Built-in Themes',
            'Visual Editor',
            'Dark & Light Mode',
            'JSON + Baked CSS',
        ],
    },
    {
        id: 'custom',
        title: 'Your Module',
        description:
            'Build and install your own modules with a single Artisan command. Full ownership — the scaffolded code lives in your repo and is yours to modify freely.',
        icon: Lightbulb,
        bg: BG_GRAY_COLOR,
        badge: 'custom',
        href: 'https://saucebase-dev.github.io/docs/fundamentals/modules',
        features: [
            'Single Command',
            'Full Ownership',
            'Any Stack',
            'Copy & Own',
        ],
    },
    {
        id: 'blog',
        title: 'Blog',
        description:
            'Full-featured blog with posts, categories, tags, and a Filament admin panel for content management.',
        icon: Newspaper,
        bg: BG_GRAY_COLOR,
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
        id: 'webhooks',
        title: 'Webhooks',
        description:
            'Send reliable HTTP callbacks to external services when events occur in your app, with delivery logs and retry handling.',
        icon: Webhook,
        bg: BG_GRAY_COLOR,
        badge: 'coming-soon',
        href: null,
        features: ['Event Triggers', 'Delivery Logs', 'Retry Handling'],
    },
    {
        id: 'integrations',
        title: 'Integrations',
        description:
            'Connect your app with third-party services like Slack, Zapier, and more through a unified integration layer.',
        icon: Blocks,
        bg: BG_GRAY_COLOR,
        badge: 'coming-soon',
        href: null,
        features: ['Slack', 'Zapier', 'Unified Layer'],
    },
    {
        id: 'notifications',
        title: 'Notifications',
        description:
            'In-app and email notifications with templates, user preferences, and delivery tracking for every channel.',
        icon: Bell,
        bg: BG_GRAY_COLOR,
        badge: 'coming-soon',
        href: null,
        features: ['In-App', 'Email', 'Templates', 'Preferences'],
    },
    {
        id: 'analytics',
        title: 'Analytics',
        description:
            'Track pageviews, custom events, and user behavior with a privacy-friendly built-in dashboard — no third-party scripts.',
        icon: BarChart3,
        bg: BG_GRAY_COLOR,
        badge: 'coming-soon',
        href: null,
        features: ['Pageviews', 'Custom Events', 'Dashboard', 'Privacy First'],
    },
];

type Module = (typeof modules)[number];

const selectedMod = ref<Module | null>(null);

// Derive CSS var from bg class: 'bg-violet-600' → { '--mod-color': 'var(--color-violet-600)' }
const modStyle = (mod: { bg: string }) => ({
    '--mod-color': `var(--color-${mod.bg.slice(3)})`,
});

const copied = ref(false);
let copyTimer: ReturnType<typeof setTimeout> | null = null;

function copyCommand() {
    if (!selectedMod.value) return;
    navigator.clipboard
        .writeText(installCommand(selectedMod.value))
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

const installCommand = (mod: Module) => {
    if (mod.badge === 'custom')
        return 'php artisan saucebase:recipe MyAmazingModuleIdea';
    return `composer require saucebase/${mod.id}`;
};
</script>

<template>
    <Head>
        <title>
            {{ $t('Saucebase | The best modular Laravel SaaS Starter Kit') }}
        </title>
        <meta
            name="description"
            :content="
                $t(
                    'Free, open-source Laravel SaaS starter kit. Ships with auth, billing, admin panel, and a modular copy-and-own architecture.',
                )
            "
        />
    </Head>
    <div class="bg-background relative isolate flex min-h-screen flex-col">
        <Header />

        <main class="mx-auto w-full px-6 py-16 lg:px-8">
            <div class="py-16">
                <h1
                    class="text-primary text-center text-6xl font-bold tracking-tight [text-shadow:0_4px_25px_color-mix(in_oklch,var(--color-primary)_15%,var(--color-background))] sm:text-7xl"
                >
                    {{ $t("Let's get started") }}
                </h1>
                <h2 class="text-secondary mt-1 text-center text-3xl font-bold">
                    {{ $t('Your foundation is ready') }}
                </h2>
                <p
                    class="text-muted-foreground mt-3 text-center text-xl leading-7"
                >
                    {{
                        $t(
                            'Focus on what makes your product unique, the modules handle the rest.',
                        )
                    }}
                </p>
            </div>
            <div
                class="relative -mt-25 overflow-hidden p-25 font-mono"
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
                    <div
                        v-for="(mod, index) in modules"
                        :key="mod.id"
                        data-card
                        class="relative cursor-pointer transition-opacity duration-200 hover:opacity-100!"
                        :class="mod.badge === 'coming-soon' ? 'opacity-50' : ''"
                        :style="[
                            modStyle(mod),
                            { '--card-delay': `${400 + index * 120}ms` },
                        ]"
                        @click="selectedMod = mod"
                    >
                        <!-- Diagonal stripe accent — stationary, fades in after card lands -->
                        <div
                            class="stripe stripe-appear absolute inset-x-2 top-9 bottom-0 w-full -translate-x-5 translate-y-2.5 rounded-xl transition-opacity duration-200"
                            :class="
                                mod.badge !== 'coming-soon'
                                    ? 'opacity-90 group-hover/card:opacity-90'
                                    : 'opacity-80'
                            "
                            :style="{
                                animationDelay:
                                    'calc(var(--card-delay) + 50ms)',
                            }"
                        />

                        <!-- Projected Shadow -->
                        <!-- <div
                            class="stripe-appear bg-foreground -skew-3 blur-md m-2 absolute inset-x-2 top-9 bottom-0 w-full -translate-x-5 translate-y-2.5 rounded-xl transition-opacity duration-200"
                            :class="
                                mod.badge !== 'coming-soon'
                                    ? 'opacity-90 group-hover/card:opacity-90'
                                    : 'opacity-80'
                            "
                            :style="{
                                animationDelay:
                                    'calc(var(--card-delay) + 50ms)',
                            }"
                        /> -->

                        <!-- Animated card (falls in) -->
                        <div
                            class="card-drop group/card relative flex flex-col pt-6"
                            :style="{ animationDelay: 'var(--card-delay)' }"
                        >
                            <div class="relative flex-1">
                                <!-- Card body -->
                                <div
                                    class="bg-card relative z-10 flex h-full flex-col gap-2 rounded-xl p-4 pt-12 text-left shadow-[-1px_1px_0_0_color-mix(in_oklch,var(--color-white)_80%,black)] transition-all duration-200 group-hover/card:translate-x-1.5 group-hover/card:-translate-y-1.5 group-hover/card:shadow-[-5px_5px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)]/70 dark:shadow-[-2px_2px_0_0_color-mix(in_oklch,var(--color-muted)_90%,black)] group-hover/card:dark:shadow-[-5px_5px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)]/70"
                                    :class="
                                        mod.badge === 'coming-soon'
                                            ? 'border-dashed'
                                            : ''
                                    "
                                >
                                    <!-- Badge (stripe background + chip) -->
                                    <template
                                        v-if="
                                            mod.badge === 'coming-soon' ||
                                            mod.badge === 'new'
                                        "
                                    >
                                        <div
                                            class="badge-stripe stripe absolute top-3 right-0.5 min-w-12 rounded-full px-2.5 py-0.5 text-[10px] font-medium opacity-50"
                                            aria-hidden="true"
                                        >
                                            <span class="invisible">
                                                {{ $t('Soon') }}
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
                                                class="flex min-w-12 items-center justify-center rounded-full border px-2.5 py-0.5 text-[10px] font-medium shadow-sm"
                                                :class="
                                                    mod.badge === 'new'
                                                        ? 'border-emerald-500 bg-emerald-100 text-emerald-600'
                                                        : 'bg-muted text-foreground'
                                                "
                                            >
                                                {{
                                                    mod.badge === 'coming-soon'
                                                        ? $t('Soon')
                                                        : $t('New')
                                                }}
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Floating icon -->
                                    <div
                                        class="bounce absolute -top-2 left-1/2 z-10 -ml-5 flex size-14 shrink-0 items-center justify-center rounded-full shadow-[-2px_2px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)] transition-all duration-200 group-hover/card:translate-x-1.5 group-hover/card:-translate-y-1.5 group-hover/card:shadow-[-5px_5px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)]"
                                        :class="mod.bg"
                                        :style="{
                                            animationDelay:
                                                'calc(var(--card-delay) + 280ms)',
                                        }"
                                    >
                                        <component
                                            :is="mod.icon"
                                            class="absolute size-7 text-black/10 transition-all duration-200 group-hover/card:text-black/30 group-hover/card:blur-[1.5px]"
                                            aria-hidden="true"
                                        />
                                        <component
                                            :is="mod.icon"
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
                                        class="text-foreground mt-4 text-center text-base leading-tight font-semibold"
                                    >
                                        {{ $t(mod.title) }}
                                    </span>
                                    <p
                                        class="text-muted-foreground line-clamp-2 text-center text-xs leading-snug"
                                    >
                                        {{ $t(mod.description) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
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
                            :style="modStyle({ bg: 'bg-foreground' })"
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

        <Footer />
    </div>

    <!-- Module detail modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-150"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div
                v-if="selectedMod"
                class="bg-background/50 fixed inset-0 z-50 backdrop-blur-sm"
                @click="selectedMod = null"
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
                    class="pointer-events-auto relative w-full max-w-lg"
                    @click.stop
                >
                    <!-- Modal card body -->
                    <div
                        class="bg-card border-border relative z-10 flex flex-col gap-3 rounded-xl border p-6 shadow-[0px_5px_0_0_color-mix(in_oklch,var(--color-white)_85%,black)] dark:shadow-[0px_5px_0_0_color-mix(in_oklch,var(--color-white)_20%,black)]"
                        :class="
                            selectedMod.badge === 'coming-soon' ||
                            selectedMod.badge === 'custom'
                                ? 'border-dashed'
                                : ''
                        "
                        :style="modStyle(selectedMod)"
                    >
                        <!-- Close button -->
                        <button
                            class="text-muted-foreground hover:text-foreground hover:border-foreground absolute top-3 right-3 z-20 cursor-pointer rounded-full border p-1.5 transition-colors"
                            @click="selectedMod = null"
                        >
                            <X class="size-4" />
                        </button>

                        <!-- Header: icon + title -->
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-full"
                                :class="selectedMod.bg"
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
                                {{ $t(selectedMod.title) }}
                            </h2>
                        </div>

                        <!-- Description -->
                        <p class="text-muted-foreground py-2 leading-relaxed">
                            {{ $t(selectedMod.description) }}
                        </p>

                        <!-- Features checklist -->
                        <ul
                            class="grid grid-cols-2 gap-x-4 gap-y-1.5 rounded-sm border p-4"
                        >
                            <li
                                v-for="feature in selectedMod.features"
                                :key="feature"
                                class="text-foreground flex items-center gap-2 text-sm"
                            >
                                <Check
                                    class="size-3.5 shrink-0"
                                    style="color: var(--mod-color)"
                                    aria-hidden="true"
                                />
                                {{ $t(feature) }}
                            </li>
                        </ul>

                        <!-- Install command (free + custom only) -->
                        <div
                            v-if="selectedMod.badge !== 'coming-soon'"
                            class="mt-2 flex flex-col gap-2"
                        >
                            <div
                                class="flex items-center gap-3 rounded-sm bg-gray-950 px-4 py-3 dark:bg-gray-900"
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
                                v-if="selectedMod.badge !== 'custom'"
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
                                v-if="selectedMod.badge !== 'coming-soon'"
                                :href="selectedMod.href ?? undefined"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex w-full items-center justify-center gap-2 rounded-full px-6 py-2.5 text-sm font-semibold text-white shadow-[0_5px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_7px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)]"
                                :class="selectedMod.bg"
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

<style scoped>
.stripe {
    --pattern-fg: color-mix(in oklch, var(--mod-color) 19%, transparent);
    background-color: color-mix(in oklch, var(--mod-color) 7%, transparent);
    background-image: repeating-linear-gradient(
        -45deg,
        var(--pattern-fg) 0px,
        var(--pattern-fg) 1.5px,
        transparent 1.5px,
        transparent 5px
    );
    border: 1px solid color-mix(in oklch, var(--mod-color) 25%, transparent);

    &::after {
        content: '';
        position: absolute;
        inset: 0;
        perspective-origin: bottom left;
        width: 105%;
        transform: translate(-4px, 6px) perspective(400px) rotateX(-25deg);
        background-color: color-mix(
            in oklch,
            var(--color-foreground) 20%,
            transparent
        );
        filter: blur(5px);
        border-radius: 2rem;
    }
    .dark &::after {
        background-color: color-mix(
            in oklch,
            var(--color-background) 50%,
            transparent
        );
    }
}

/* Badge stripe uses foreground color regardless of module color */
.badge-stripe {
    --mod-color: var(--color-foreground);
}

@keyframes bounce {
    0% {
        transform: translate(0, 0);
    }
    25% {
        transform: translate(-3px, 3px);
    }
    55% {
        transform: translate(3px, -3px);
    }
    72% {
        transform: translate(3px, -3px);
    }
    100% {
        transform: translate(0, 0);
    }
}

.bounce {
    animation: bounce 0.7s ease-in-out backwards;
}

@keyframes stripe-appear {
    from {
        opacity: 0;
        scale: 0.5;
        transform: translateY(24px);
    }
}

.stripe-appear {
    transform-origin: left center;
    animation: stripe-appear 0.6s ease-out backwards;
}

@keyframes card-drop-move {
    0% {
        transform: translateY(-100%) translateX(30%) scale(1.25);
    }
    100% {
        transform: translateY(0) translateX(0) scale(1);
    }
}

@keyframes card-drop-fade {
    0% {
        opacity: 0;
    }
    35% {
        opacity: 1;
    }
    100% {
        opacity: 1;
    }
}

.card-drop {
    animation:
        card-drop-move 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) both,
        card-drop-fade 0.4s ease-out both;
}
</style>
