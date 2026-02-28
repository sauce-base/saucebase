export const INTERVAL_CONFIG: Record<
    string,
    { display: string; label: string; normalized: string }
> = {
    day: { display: '/day', label: 'Daily', normalized: 'day' },
    daily: { display: '/day', label: 'Daily', normalized: 'day' },
    week: { display: '/week', label: 'Weekly', normalized: 'week' },
    weekly: { display: '/week', label: 'Weekly', normalized: 'week' },
    month: { display: '/month', label: 'Monthly', normalized: 'month' },
    monthly: { display: '/month', label: 'Monthly', normalized: 'month' },
    year: { display: '/year', label: 'Yearly', normalized: 'year' },
    yearly: { display: '/year', label: 'Yearly', normalized: 'year' },
};

export function getIntervalDisplay(interval: string | null): string {
    if (!interval) return 'one-time';
    return INTERVAL_CONFIG[interval]?.display || `/${interval}`;
}

export function normalizeInterval(interval: string | null): string {
    if (!interval) return 'one_time';
    return INTERVAL_CONFIG[interval]?.normalized || interval;
}

export function matchesInterval(
    priceInterval: string | null,
    selectedInterval: string,
): boolean {
    return normalizeInterval(priceInterval) === selectedInterval;
}

export function getIntervalLabel(interval: string): string {
    return INTERVAL_CONFIG[interval]?.label || interval;
}
