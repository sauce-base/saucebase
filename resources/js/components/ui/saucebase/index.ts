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
    label: 'SOON',
    class: 'bg-muted text-foreground',
} as const;

export const modules = [
    {
        id: 'auth',
        title: 'Auth',
        description:
            'Complete authentication system with login, registration, magic link (passwordless), password reset, email verification, and OAuth integration (Google, GitHub).',
        icon: Lock,
        color: '--color-violet-600',
        badge: null,
        href: 'https://saucebase-dev.github.io/docs/modules/auth',
        features: [
            'Login & Register',
            'Magic Link',
            'Social Login',
            'Email Verification',
            'Impersonation',
        ],
    },
    {
        id: 'settings',
        title: 'Settings',
        description:
            'Account settings pages for managing profile info, avatar, password, and connected social accounts.',
        icon: Settings2,
        color: '--color-sky-500',
        badge: null,
        href: 'https://saucebase-dev.github.io/docs/modules/settings',
        features: [
            'Profile Info',
            'Avatar Upload',
            'Password Change',
            'Connected Accounts',
        ],
    },
    {
        id: 'billing',
        title: 'Billing',
        description:
            'Subscription management and payment processing via Stripe with checkout sessions, billing portal, invoices, and webhook processing.',
        icon: CreditCard,
        color: '--color-green-600',
        badge: null,
        href: 'https://saucebase-dev.github.io/docs/modules/billing',
        features: ['Checkout', 'Subscriptions', 'Billing Portal', 'Webhooks'],
    },
    {
        id: 'roadmap',
        title: 'Roadmap',
        description:
            'Public roadmap with feature requests, voting, moderation, six statuses, and a Filament admin panel.',
        icon: Map,
        color: '--color-amber-500',
        badge: null,
        href: 'https://saucebase-dev.github.io/docs/modules/roadmap',
        features: ['Feature Requests', 'Voting', 'Sorting', 'Admin Panel'],
    },
    {
        id: 'announcements',
        title: 'Announcements',
        description:
            'Site-wide announcement banners with scheduling, audience targeting, and cookie-based dismissal, managed from the Filament admin panel.',
        icon: Megaphone,
        color: '--color-indigo-500',
        badge: null,
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
        color: '--color-purple-500',
        badge: {
            label: 'NEW',
            class: 'border-emerald-600 bg-emerald-600 text-background',
        },
        href: 'https://saucebase-dev.github.io/docs/modules/themes',
        features: [
            '15 Built-in Themes',
            'Visual Editor',
            'Dark & Light Mode',
            'Baked CSS',
        ],
    },
    {
        id: 'custom',
        title: 'Your Module',
        description:
            'Build and install your own modules with a single Artisan command. Full ownership — the scaffolded code lives in your repo and is yours to modify freely.',
        icon: Lightbulb,
        color: '--secondary',
        badge: {
            label: 'FOCUS HERE',
            class: 'bg-destructive text-destructive-foreground border-destructive',
        },
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
        color: '--color-gray-500',
        badge: BADGE_SOON,
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
        color: '--color-gray-500',
        badge: BADGE_SOON,
        href: null,
        features: [
            'Event Triggers',
            'Delivery Logs',
            'Retry Handling',
            'Failure Alerts',
        ],
    },
    {
        id: 'integrations',
        title: 'Integrations',
        description:
            'Connect your app with third-party services like Slack, Zapier, and more through a unified integration layer.',
        icon: Blocks,
        color: '--color-gray-500',
        badge: BADGE_SOON,
        href: null,
        features: ['Slack', 'Zapier', 'Custom Connections', 'Unified Layer'],
    },
    {
        id: 'notifications',
        title: 'Notifications',
        description:
            'In-app and email notifications with templates, user preferences, and delivery tracking for every channel.',
        icon: Bell,
        color: '--color-gray-500',
        badge: BADGE_SOON,
        href: null,
        features: ['In-App', 'Email', 'Templates', 'Preferences'],
    },
    {
        id: 'analytics',
        title: 'Analytics',
        description:
            'Track pageviews, custom events, and user behavior with a privacy-friendly built-in dashboard — no third-party scripts.',
        icon: BarChart3,
        color: '--color-gray-500',
        badge: BADGE_SOON,
        href: null,
        features: ['Pageviews', 'Custom Events', 'Dashboard', 'Privacy First'],
    },
];

export type Module = (typeof modules)[number];

export { default as ModuleCard } from './ModuleCard.vue';
export { default as ModuleModal } from './ModuleModal.vue';
