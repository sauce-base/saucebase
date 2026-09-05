import { useModules } from '@/hooks/useModules';
import type { LucideIcon } from 'lucide-react';
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
    Webhook,
} from 'lucide-react';

const BADGE_SOON = {
    label: 'SOON',
    class: 'bg-muted text-foreground',
} as const;

const NEW_BADGE = {
    label: 'NEW',
    class: 'border-emerald-600 bg-emerald-600 text-background',
} as const;

const BADGE_ADDED = {
    label: 'Added',
    class: 'border-primary bg-primary text-primary-foreground',
} as const;

export function useModuleList() {
    const { has } = useModules();
    return modules.map((m) => ({
        ...m,
        badge: has(m.id) ? BADGE_ADDED : m.badge,
    }));
}

export interface ModuleBadge {
    label: string;
    class: string;
}

export interface Module {
    id: string;
    title: string;
    description: string;
    icon: LucideIcon;
    color: string;
    badge: ModuleBadge | null;
    href: string | null;
    frameworks: readonly ('vue' | 'react')[];
    features: string[];
    customCommands?: readonly string[];
}

export const modules: Module[] = [
    {
        id: 'custom',
        title: 'Your Module',
        description:
            'Build and install your own modules with a single Artisan command. Full ownership, the scaffolded code lives in your repo and is yours to modify freely.',
        icon: Lightbulb,
        color: '--secondary',
        badge: {
            label: 'FOCUS HERE',
            class: 'bg-destructive text-destructive-foreground border-destructive',
        },
        href: 'https://saucebase-dev.github.io/docs/fundamentals/modules',
        frameworks: ['vue', 'react'] as const,
        features: [
            'Single Command',
            'Full Ownership',
            'Any Stack',
            'Copy & Own',
        ],
    },
    {
        id: 'auth',
        customCommands: ['git apply modules/auth/patches/user.patch'],
        title: 'Auth',
        description:
            'Complete authentication system with login, registration, magic link (passwordless), password reset, email verification, and OAuth integration (Google, GitHub).',
        icon: Lock,
        color: '--color-violet-600',
        badge: NEW_BADGE,
        href: 'https://saucebase-dev.github.io/docs/modules/auth',
        frameworks: ['vue', 'react'] as const,
        features: [
            'Login & Register',
            'Magic Link',
            'Social Login',
            'Email Verification',
            'Impersonation',
            'Profile & Avatar',
            'Password Change',
        ],
    },
    {
        id: 'billing',
        customCommands: ['git apply modules/billing/patches/user.patch'],
        title: 'Billing',
        description:
            'Subscription management and payment processing via Stripe with checkout sessions, billing portal, invoices, and webhook processing.',
        icon: CreditCard,
        color: '--color-gray-500',
        badge: BADGE_SOON,
        href: null,
        frameworks: ['vue'] as const,
        features: ['Checkout', 'Subscriptions', 'Billing Portal', 'Webhooks'],
    },
    {
        id: 'roadmap',
        title: 'Roadmap',
        description:
            'Public roadmap with feature requests, voting, moderation, six statuses, and a Filament admin panel.',
        icon: Map,
        color: '--color-gray-500',
        badge: BADGE_SOON,
        href: null,
        frameworks: ['vue'] as const,
        features: ['Feature Requests', 'Voting', 'Sorting', 'Admin Panel'],
    },
    {
        id: 'announcements',
        title: 'Announcements',
        description:
            'Site-wide announcement banners with scheduling, audience targeting, and cookie-based dismissal, managed from the Filament admin panel.',
        icon: Megaphone,
        color: '--color-gray-500',
        badge: BADGE_SOON,
        href: null,
        frameworks: ['vue'] as const,
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
        color: '--color-gray-500',
        badge: BADGE_SOON,
        href: null,
        frameworks: ['vue'] as const,
        features: [
            '15 Built-in Themes',
            'Visual Editor',
            'Dark & Light Mode',
            'Baked CSS',
        ],
    },
    {
        id: 'blog',
        title: 'Blog',
        description:
            'Full-featured blog with posts, categories, cover images, SEO metadata, and a Filament admin panel for content management.',
        icon: Newspaper,
        color: '--color-gray-500',
        badge: BADGE_SOON,
        href: null,
        frameworks: ['vue'] as const,
        features: ['Posts', 'Categories', 'Admin Panel', 'SEO Optimized'],
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
        frameworks: ['vue'] as const,
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
        frameworks: ['vue'] as const,
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
        frameworks: ['vue'] as const,
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
        frameworks: ['vue'] as const,
        features: ['Pageviews', 'Custom Events', 'Dashboard', 'Privacy First'],
    },
];

export { ModuleCard } from './ModuleCard';
export { ModuleModal } from './ModuleModal';
