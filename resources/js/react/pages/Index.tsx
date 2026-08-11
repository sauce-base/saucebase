import type { Module } from '@/components/ui/saucebase';
import {
    ModuleCard,
    ModuleModal,
    useModuleList,
} from '@/components/ui/saucebase';
import { useSettings } from '@/hooks/useSettings';
import { useT } from '@/i18n';
import SiteLayout from '@/layouts/SiteLayout';
import { BookOpen } from 'lucide-react';
import { useState } from 'react';

export default function Index() {
    const t = useT();
    const settings = useSettings();
    const modules = useModuleList();
    const [selectedMod, setSelectedMod] = useState<Module | null>(null);

    return (
        <SiteLayout title={settings.general.site_tagline ?? undefined}>
            <main className="mx-auto w-full">
                <div className="relative overflow-hidden mask-t-from-95% mask-b-from-95% px-6 md:mask-r-from-95% md:mask-l-from-95% md:px-16 lg:px-8">
                    <div className="mt-6 pt-24 pb-12">
                        <h1 className="text-foreground/80 dark:text-muted-foreground text-center text-4xl font-bold [text-shadow:0_4px_25px_color-mix(in_oklch,var(--color-primary)_15%,var(--color-background))] md:text-5xl">
                            {t('Your foundation is ready!')}
                        </h1>
                        <h2 className="text-secondary mt-1 text-center text-5xl font-bold md:text-7xl">
                            {t("Let's get started")}
                        </h2>
                        <p className="text-muted-foreground mt-3 text-center text-xl tracking-tighter md:text-3xl">
                            {t(
                                'Your recipe first. Modules for everything else',
                            )}
                        </p>
                    </div>

                    {/* Module cards */}
                    <div className="relative z-10 mx-auto grid max-w-6xl grid-cols-1 gap-8 gap-y-2 px-6 pt-8 pb-16 font-mono has-[[data-card]:hover]:*:data-card:opacity-40 sm:grid-cols-2 sm:px-10 lg:grid-cols-3 lg:px-20 xl:grid-cols-4">
                        {modules.map((mod, index) => (
                            <ModuleCard
                                key={mod.id}
                                module={mod}
                                index={index}
                                moduleClass="rotate-[-5deg] skew-x-10"
                                onSelect={setSelectedMod}
                            />
                        ))}
                    </div>

                    {/* Light mode dot pattern */}
                    <div
                        className="absolute inset-0 -top-10 -right-20 -bottom-10 -left-20 -z-1 overflow-hidden md:rotate-[-5deg] md:skew-x-10 dark:hidden"
                        style={{
                            backgroundSize: '24px',
                            backgroundPosition: 'top left',
                            backgroundImage:
                                "url('data:image/svg+xml,%3Csvg viewBox=%220 0 32 32%22 fill=%22none%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg opacity=%22.4%22 fill=%22%23011E32%22 fill-opacity=%22.24%22%3E%3Cpath fill-rule=%22evenodd%22 clip-rule=%22evenodd%22 d=%22M0 .5V6h.5V.5H6V0H0v.5ZM.5 32H0v-6h.5v5.5H6v.5H.5ZM32 0v6h-.5V.5H26V0h6Zm0 31.5V26h-.5v5.5H26v.5h6v-.5Z%22/%3E%3Cpath opacity=%22.6%22 d=%22M19 0v.5h-6V0zM19 31.5v.5h-6v-.5zM32 19h-.5v-6h.5zM.5 19H0v-6h.5z%22/%3E%3C/g%3E%3C/svg%3E')",
                        }}
                    />
                    {/* Dark mode dot pattern */}
                    <div
                        className="absolute inset-0 -top-10 -right-20 -bottom-10 -left-20 -z-1 hidden overflow-hidden md:rotate-[-5deg] md:skew-x-10 dark:block"
                        style={{
                            backgroundSize: '24px',
                            backgroundPosition: 'top left',
                            backgroundImage:
                                "url('data:image/svg+xml,%3Csvg viewBox=%220 0 32 32%22 fill=%22none%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg opacity=%22.5%22 fill=%22%23ffffff%22 fill-opacity=%22.15%22%3E%3Cpath fill-rule=%22evenodd%22 clip-rule=%22evenodd%22 d=%22M0 .5V6h.5V.5H6V0H0v.5ZM.5 32H0v-6h.5v5.5H6v.5H.5ZM32 0v6h-.5V.5H26V0h6Zm0 31.5V26h-.5v5.5H26v.5h6v-.5Z%22/%3E%3Cpath opacity=%22.6%22 d=%22M19 0v.5h-6V0zM19 31.5v.5h-6v-.5zM32 19h-.5v-6h.5zM.5 19H0v-6h.5z%22/%3E%3C/g%3E%3C/svg%3E')",
                        }}
                    />

                    <div className="my-8 mb-36 flex justify-center">
                        <div className="relative inline-flex">
                            <div
                                className="stripe absolute inset-0 translate-y-3 rounded-full"
                                style={
                                    {
                                        '--mod-color': 'var(--foreground)',
                                    } as React.CSSProperties
                                }
                            />
                            <a
                                href="https://saucebase-dev.github.io/docs/"
                                className="hover:bg-foreground/80 text-background bg-foreground/90 relative flex items-center gap-2 rounded-full px-8 py-4 text-base font-semibold shadow-[0_5px_0_0_color-mix(in_oklch,var(--color-foreground)_85%,black)] transition-all duration-200 hover:-translate-y-1 hover:shadow-[0_9px_0_0_color-mix(in_oklch,var(--color-foreground)_85%,black)]"
                            >
                                <BookOpen
                                    className="size-5"
                                    aria-hidden="true"
                                />
                                {t('Read the Documentation')}
                            </a>
                        </div>
                    </div>
                </div>
            </main>

            <ModuleModal
                selectedMod={selectedMod}
                onClose={() => setSelectedMod(null)}
            />
        </SiteLayout>
    );
}
