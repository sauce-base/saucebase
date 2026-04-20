import { trans } from 'laravel-vue-i18n';
import './modules.css';

import {
    BarChart3,
    Bell,
    Blocks,
    CreditCard,
    Lightbulb,
    Lock,
    Map,
    Megaphone,
    Newspaper,
    Palette,
    Settings2,
    Webhook,
} from 'lucide-vue-next';

const BADGE_SOON = {
    label: () => trans('SOON'),
    class: 'bg-muted text-foreground',
} as const;

const NEW_BADGE = {
    label: () => trans('NEW'),
    class: 'border-emerald-600 bg-emerald-600 text-background',
} as const;

export const modules = [
    {
        id: 'custom',
        title: () => trans('Your Module'),
        description:
            () => trans('Build and install your own modules with a single Artisan command. Full ownership, the scaffolded code lives in your repo and is yours to modify freely.'),
        icon: Lightbulb,
        color: '--secondary',
        badge: {
            label: () => trans('FOCUS HERE'),
            class: 'bg-destructive text-destructive-foreground border-destructive',
        },
        href: 'https://saucebase-dev.github.io/docs/fundamentals/modules',
        features: [
            () => trans('Single Command'),
            () => trans('Full Ownership'),
            () => trans('Any Stack'),
            () => trans('Copy & Own'),
        ],
    },
    {
        id: 'auth',
        title: () => trans('Auth'),
        description:
            () => trans('Complete authentication system with login, registration, magic link (passwordless), password reset, email verification, and OAuth integration (Google, GitHub).'),
        icon: Lock,
        color: '--color-violet-600',
        badge: null,
        href: 'https://saucebase-dev.github.io/docs/modules/auth',
        features: [
            () => trans('Login & Register'),
            () => trans('Magic Link'),
            () => trans('Social Login'),
            () => trans('Email Verification'),
            () => trans('Impersonation'),
        ],
    },
    {
        id: 'settings',
        title: () => trans('Settings'),
        description:
            () => trans('Account settings pages for managing profile info, avatar, password, and connected social accounts.'),
        icon: Settings2,
        color: '--color-sky-500',
        badge: null,
        href: 'https://saucebase-dev.github.io/docs/modules/settings',
        features: [
            () => trans('Profile Info'),
            () => trans('Avatar Upload'),
            () => trans('Password Change'),
            () => trans('Connected Accounts'),
        ],
    },
    {
        id: 'billing',
        title: () => trans('Billing'),
        description:
            () => trans('Subscription management and payment processing via Stripe with checkout sessions, billing portal, invoices, and webhook processing.'),
        icon: CreditCard,
        color: '--color-green-600',
        badge: null,
        href: 'https://saucebase-dev.github.io/docs/modules/billing',
        features: [
            () => trans('Checkout'),
            () => trans('Subscriptions'),
            () => trans('Billing Portal'),
            () => trans('Webhooks'),
        ],
    },
    {
        id: 'roadmap',
        title: () => trans('Roadmap'),
        description:
            () => trans('Public roadmap with feature requests, voting, moderation, six statuses, and a Filament admin panel.'),
        icon: Map,
        color: '--color-amber-500',
        badge: null,
        href: 'https://saucebase-dev.github.io/docs/modules/roadmap',
        features: [
            () => trans('Feature Requests'),
            () => trans('Voting'),
            () => trans('Sorting'),
            () => trans('Admin Panel'),
        ],
    },
    {
        id: 'announcements',
        title: () => trans('Announcements'),
        description:
            () => trans('Site-wide announcement banners with scheduling, audience targeting, and cookie-based dismissal, managed from the Filament admin panel.'),
        icon: Megaphone,
        color: '--color-indigo-500',
        badge: null,
        href: 'https://saucebase-dev.github.io/docs/modules/announcements',
        features: [
            () => trans('Banner'),
            () => trans('Scheduling'),
            () => trans('Audience Targeting'),
            () => trans('Dismissal'),
            () => trans('Admin Panel'),
        ],
    },
    {
        id: 'themes',
        title: () => trans('Themes'),
        description:
            () => trans("Visual theme editor for designing your app's colors, fonts, radius, and shadows. Pick a built-in theme or build your own, then bake it into CSS - no runtime overhead."),
        icon: Palette,
        color: '--color-purple-500',
        badge: NEW_BADGE,
        href: 'https://saucebase-dev.github.io/docs/modules/themes',
        features: [
            () => trans('15 Built-in Themes'),
            () => trans('Visual Editor'),
            () => trans('Dark & Light Mode'),
            () => trans('Baked CSS'),
        ],
    },

    {
        id: 'blog',
        title: () => trans('Blog'),
        description:
            () => trans('Full-featured blog with posts, categories, cover images, SEO metadata, and a Filament admin panel for content management.'),
        icon: Newspaper,
        color: '--color-rose-500',
        badge: NEW_BADGE,
        href: 'https://saucebase-dev.github.io/docs/modules/blog',
        features: [
            () => trans('Posts'),
            () => trans('Categories'),
            () => trans('Admin Panel'),
            () => trans('SEO Optimized'),
        ],
    },
    {
        id: 'webhooks',
        title: () => trans('Webhooks'),
        description:
            () => trans('Send reliable HTTP callbacks to external services when events occur in your app, with delivery logs and retry handling.'),
        icon: Webhook,
        color: '--color-gray-500',
        badge: BADGE_SOON,
        href: null,
        features: [
            () => trans('Event Triggers'),
            () => trans('Delivery Logs'),
            () => trans('Retry Handling'),
            () => trans('Failure Alerts'),
        ],
    },
    {
        id: 'integrations',
        title: () => trans('Integrations'),
        description:
            () => trans('Connect your app with third-party services like Slack, Zapier, and more through a unified integration layer.'),
        icon: Blocks,
        color: '--color-gray-500',
        badge: BADGE_SOON,
        href: null,
        features: [
            () => trans('Slack'),
            () => trans('Zapier'),
            () => trans('Custom Connections'),
            () => trans('Unified Layer'),
        ],
    },
    {
        id: 'notifications',
        title: () => trans('Notifications'),
        description:
            () => trans('In-app and email notifications with templates, user preferences, and delivery tracking for every channel.'),
        icon: Bell,
        color: '--color-gray-500',
        badge: BADGE_SOON,
        href: null,
        features: [
            () => trans('In-App'),
            () => trans('Email'),
            () => trans('Templates'),
            () => trans('Preferences'),
        ],
    },
    {
        id: 'analytics',
        title: () => trans('Analytics'),
        description:
            () => trans('Track pageviews, custom events, and user behavior with a privacy-friendly built-in dashboard — no third-party scripts.'),
        icon: BarChart3,
        color: '--color-gray-500',
        badge: BADGE_SOON,
        href: null,
        features: [
            () => trans('Pageviews'),
            () => trans('Custom Events'),
            () => trans('Dashboard'),
            () => trans('Privacy First'),
        ],
    },
];

export type Module = (typeof modules)[number];

export { default as ModuleCard } from './ModuleCard.vue';
export { default as ModuleModal } from './ModuleModal.vue';
