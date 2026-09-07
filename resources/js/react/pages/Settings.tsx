import { useSettings } from '@/hooks/useSettings';
import { useSettingsModal } from '@/hooks/useSettingsModal';
import { useT } from '@/i18n';
import { resolveIcon } from '@/lib/navigation';
import { cn, resolveModularPageComponent } from '@/lib/utils';
import { router } from '@inertiajs/react';
import { Modal, useModal } from '@inertiaui/modal-react';
import type { SettingsSection } from '@js/settings';
import { X } from 'lucide-react';
import { useEffect, useState, type ComponentType } from 'react';

interface SettingsProps {
    activeSection: string | null;
    sectionProps: Record<string, Record<string, unknown>>;
}

export default function Settings({
    activeSection,
    sectionProps,
}: SettingsProps) {
    const t = useT();
    const settings = useSettings();
    const { section: fragmentSection, open, restore } = useSettingsModal();
    const modal = useModal();

    const sections: SettingsSection[] = settings.sections ?? [];

    /** The fragment wins; the server prop is the fallback for the first render. */
    const current =
        sections.find(
            (item) => item.slug === (fragmentSection ?? activeSection),
        ) ?? sections[0];

    const slug = current?.slug;
    const component = current?.component;

    const [panel, setPanel] = useState<ComponentType | null>(null);

    useEffect(() => {
        if (!component) {
            setPanel(null);

            return;
        }

        let stale = false;

        resolveModularPageComponent(component).then((resolved) => {
            if (stale) {
                return;
            }

            // Wrapped in a callback: `setState` would otherwise treat the
            // component function as a state updater and call it.
            setPanel(
                () =>
                    (resolved as { default?: ComponentType }).default ??
                    (resolved as unknown as ComponentType),
            );
        });

        return () => {
            stale = true;
        };
    }, [component]);

    /**
     * Sections other than the one first requested arrive as optional props, so
     * the first time one is shown its data is fetched with a partial reload.
     * Reload targets the same URL, which is all the modal supports, so the
     * panel swap happens without leaving or restacking the modal.
     */
    useEffect(() => {
        if (!slug || sectionProps?.[slug] !== undefined) {
            return;
        }

        modal?.reload({ only: [`sectionProps.${slug}`] });
    }, [slug, sectionProps, modal]);

    /**
     * Refresh the active section after a panel saves something.
     *
     * Panels post to their own routes, which update the *page* props behind the
     * modal. The panel itself renders from the *modal's* props, so without this
     * a save would leave the panel showing stale data — an uploaded avatar, for
     * instance, would never reveal its remove button. Reload uses the modal's
     * own XHR client rather than the router, so this cannot feed back into
     * itself.
     */
    useEffect(
        () =>
            router.on('success', () => {
                if (!slug) {
                    return;
                }

                modal?.reload({
                    only: [`sectionProps.${slug}`],
                    onSuccess: () => restore(slug),
                });
                restore(slug);
            }),
        [slug, modal, restore],
    );

    const panelProps = sectionProps?.[slug ?? ''];
    const Panel = panel;

    /**
     * A section's props arrive with the modal only when it was the one
     * requested; the rest are fetched on first view. Panels are written against
     * their own data, so one must not be handed an empty object while that
     * request is in flight.
     */
    const isPanelReady = Panel !== null && panelProps !== undefined;

    return (
        <Modal maxWidth="4xl" paddingClasses="" closeButton={false}>
            {/*
                `tabIndex={0}` on the panel itself, rather than a focus call that
                has to beat the modal's focus trap: the trap focuses the first
                focusable element in the dialog, so being that element is what
                keeps a focus ring off whichever control happens to come first.
            */}
            <div
                tabIndex={0}
                className="flex h-[80vh] outline-none"
                data-testid="settings-modal"
            >
                {/* Section navigation */}
                <nav
                    className="bg-muted/40 w-56 shrink-0 overflow-y-auto border-r p-3"
                    data-testid="settings-sidebar"
                >
                    <p className="text-muted-foreground px-3 pt-2 pb-3 text-xs font-medium tracking-wide uppercase">
                        {t('Settings')}
                    </p>
                    <ul className="space-y-1">
                        {sections.map((item) => {
                            const Icon = item.icon
                                ? resolveIcon(item.icon)
                                : undefined;

                            return (
                                <li key={item.slug}>
                                    <button
                                        type="button"
                                        data-testid={`settings-section-${item.slug}`}
                                        aria-current={
                                            slug === item.slug
                                                ? 'page'
                                                : undefined
                                        }
                                        className={cn(
                                            'flex w-full cursor-pointer items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors',
                                            slug === item.slug
                                                ? 'bg-accent text-accent-foreground font-medium'
                                                : 'text-muted-foreground hover:bg-accent/50 hover:text-foreground',
                                        )}
                                        onClick={() => open(item.slug)}
                                    >
                                        {Icon && (
                                            <Icon className="size-4 shrink-0" />
                                        )}
                                        {t(item.title)}
                                    </button>
                                </li>
                            );
                        })}
                    </ul>
                </nav>

                {/* Active section */}
                <div className="flex min-w-0 flex-1 flex-col">
                    {/* Fixed header: the close control must not scroll away with
                        the section, and only the section below it scrolls. */}
                    <div className="flex shrink-0 justify-end p-3">
                        <button
                            type="button"
                            className="text-muted-foreground hover:bg-accent hover:text-foreground focus-visible:ring-ring cursor-pointer rounded-md p-1.5 transition-colors focus-visible:ring-2 focus-visible:outline-none"
                            data-testid="settings-close"
                            aria-label={t('Close')}
                            onClick={() => modal?.close()}
                        >
                            <X className="size-4" />
                        </button>
                    </div>

                    <div className="flex-1 overflow-y-auto px-6 pb-6">
                        {isPanelReady ? (
                            <Panel key={slug} {...panelProps} />
                        ) : current ? (
                            /* Section data still loading */
                            <div
                                className="space-y-4"
                                aria-busy="true"
                                data-testid="settings-panel-loading"
                            >
                                <div className="bg-muted h-6 w-40 animate-pulse rounded" />
                                <div className="bg-muted h-4 w-64 animate-pulse rounded" />
                                <div className="bg-muted h-32 w-full animate-pulse rounded-lg" />
                            </div>
                        ) : (
                            <div className="text-muted-foreground flex h-full items-center justify-center text-sm">
                                {t('Select a section')}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </Modal>
    );
}
