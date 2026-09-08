import { visitModal, type ModalInstance } from '@inertiaui/modal-vue';
import { computed, onMounted, ref, watch } from 'vue';

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

const fragment = ref(typeof window === 'undefined' ? '' : window.location.hash);

function readFragment() {
    fragment.value = window.location.hash;
}

/**
 * Bound once for the lifetime of the app rather than per component. Both the
 * sync below and the modal itself read the fragment, and the modal unmounts every
 * time it closes — tearing the listener down with it would leave the fragment
 * unwatched and settings unable to reopen.
 */
if (typeof window !== 'undefined') {
    window.addEventListener('hashchange', readFragment);
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
    const parsed = computed(() => parse(fragment.value));

    function open(section?: string) {
        window.location.hash = settingsHref(section);
    }

    /**
     * Drop the fragment without adding a history entry, so closing the modal
     * does not leave a dead step the back button has to walk through.
     */
    function close() {
        if (!parse(window.location.hash).open) {
            return;
        }

        window.history.replaceState(
            window.history.state,
            '',
            window.location.pathname + window.location.search,
        );
        readFragment();
    }

    /**
     * Put the fragment back after something else rewrote the URL.
     *
     * Inertia owns the history entry and knows nothing about this fragment, so
     * any visit it makes while the modal is open drops it. `replaceState` is
     * used rather than assigning `location.hash` because assigning would fire
     * `hashchange` and re-enter the open/close cycle.
     */
    function restore(section?: string) {
        const target = settingsHref(section);

        if (window.location.hash === target) {
            return;
        }

        window.history.replaceState(
            window.history.state,
            '',
            window.location.pathname + window.location.search + target,
        );
        readFragment();
    }

    return {
        isOpen: computed(() => parsed.value.open),
        section: computed(() => parsed.value.section),
        open,
        close,
        restore,
    };
}

/**
 * Drives the modal itself from the fragment the rest of this file reads.
 *
 * Unlike {@see useSettingsModal}, this has two constraints the call site has to
 * respect: it must be called **once**, from the app root — a second call opens a
 * second modal — and it must be called **inside setup**, because of the
 * `onMounted` below.
 *
 * A composable rather than a component, even though it only runs effects: Vue
 * has no such component. A template-less SFC warns about its missing render
 * function, and `vue/valid-template-root` refuses the empty template that would
 * silence it. React keeps a `SettingsModal` component because there the same
 * code is hooks returning `null`, which React accepts.
 */
export function useSettingsModalSync(): void {
    const { isOpen, section, close } = useSettingsModal();

    let modal: ModalInstance | null = null;
    let opening = false;

    async function openModal(activeSection: string | null) {
        if (modal || opening) {
            return;
        }

        opening = true;

        try {
            modal = await visitModal(
                route(
                    'settings',
                    activeSection ? { section: activeSection } : {},
                ),
                {
                    // Closing from inside the modal (escape, backdrop, close
                    // button) has to clear the fragment too, or it could not be
                    // reopened.
                    onClose: () => {
                        modal = null;
                        close();
                    },
                },
            );
        } finally {
            opening = false;
        }
    }

    /**
     * Deferred to mount, not started in setup. A deep link to `#settings/...`
     * would otherwise visit before Inertia has settled and take a 409 back,
     * leaving the modal closed. This is what being a child component used to buy
     * implicitly.
     */
    onMounted(() => {
        watch(
            isOpen,
            (open) => {
                if (open) {
                    openModal(section.value);
                } else {
                    modal?.close();
                    modal = null;
                }
            },
            { immediate: true },
        );
    });
}
