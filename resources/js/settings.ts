import type { Page } from '@inertiajs/core';

export type Settings = {
    general: {
        site_name: string;
        site_tagline: string | null;
        site_description: string | null;
        site_icon: string | null;
        site_logo: string | null;
        prefer_logo: boolean;
    };
    [domain: string]: unknown;
};

/**
 * Global Inertia title callback: suffixes each page title with the site name.
 *
 * Pages without a title fall back to the site name alone, so the document
 * never renders a bare separator or an empty <title>.
 */
export function siteTitle(title: string, page: Page): string {
    const siteName = page.props.settings?.general?.site_name ?? '';

    if (!title) {
        return siteName;
    }

    return siteName ? `${title} - ${siteName}` : title;
}
