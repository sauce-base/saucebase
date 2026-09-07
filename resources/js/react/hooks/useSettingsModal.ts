import { useCallback, useMemo, useSyncExternalStore } from 'react';

/**
 * Whether the settings modal is open, which section it shows, and how to change
 * either — all held in the URL fragment, e.g. `#settings/profile`.
 *
 * A fragment is used rather than a route because settings open *over* whatever
 * page the user is on: it never reaches the server, so no navigation happens and
 * the page behind the modal is left untouched. It still gives shareable links
 * and working back/forward buttons.
 */
const PREFIX = 'settings';

/**
 * Address of a section, for links that open settings rather than navigate.
 *
 * A plain `href` rather than an Inertia visit: the fragment is client-side state,
 * so the anchor must stay an anchor.
 */
export function settingsHref(section?: string): string {
    return section ? `#${PREFIX}/${section}` : `#${PREFIX}`;
}

const listeners = new Set<() => void>();

/**
 * `replaceState` does not fire `hashchange`, so the two writers below have to
 * announce themselves; the browser event feeds the same channel.
 */
function notify(): void {
    for (const listener of listeners) {
        listener();
    }
}

/**
 * Bound once for the lifetime of the app rather than per component. Both the
 * opener and the modal itself read the fragment, and the modal unmounts every
 * time it closes — tearing the listener down with it would leave the fragment
 * unwatched and settings unable to reopen.
 */
if (typeof window !== 'undefined') {
    window.addEventListener('hashchange', notify);
}

function subscribe(listener: () => void): () => void {
    listeners.add(listener);

    return () => {
        listeners.delete(listener);
    };
}

/** Parse `#settings/profile` into its section, or null when not a settings link. */
function parse(hash: string): { open: boolean; section: string | null } {
    const value = hash.replace(/^#/, '');

    if (value !== PREFIX && !value.startsWith(`${PREFIX}/`)) {
        return { open: false, section: null };
    }

    const section = value.slice(PREFIX.length + 1);

    return { open: true, section: section === '' ? null : section };
}

export function useSettingsModal() {
    const fragment = useSyncExternalStore(
        subscribe,
        () => window.location.hash,
        () => '',
    );

    const parsed = useMemo(() => parse(fragment), [fragment]);

    const open = useCallback((section?: string) => {
        window.location.hash = settingsHref(section);
    }, []);

    /**
     * Drop the fragment without adding a history entry, so closing the modal
     * does not leave a dead step the back button has to walk through.
     */
    const close = useCallback(() => {
        if (!parse(window.location.hash).open) {
            return;
        }

        window.history.replaceState(
            window.history.state,
            '',
            window.location.pathname + window.location.search,
        );
        notify();
    }, []);

    /**
     * Put the fragment back after something else rewrote the URL.
     *
     * Inertia owns the history entry and knows nothing about this fragment, so
     * any visit it makes while the modal is open drops it. `replaceState` is
     * used rather than assigning `location.hash` because assigning would fire
     * `hashchange` and re-enter the open/close cycle.
     */
    const restore = useCallback((section?: string) => {
        const target = settingsHref(section);

        if (window.location.hash === target) {
            return;
        }

        window.history.replaceState(
            window.history.state,
            '',
            window.location.pathname + window.location.search + target,
        );
        notify();
    }, []);

    return {
        isOpen: parsed.open,
        section: parsed.section,
        open,
        close,
        restore,
    };
}
