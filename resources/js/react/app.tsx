import App from '@/components/App';
import { initializeTheme } from '@/hooks/useTheme';
import { I18nProvider } from '@/i18n';
import { getGlobalComponents } from '@/lib/globalComponents';
import '@css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import {
    initFromPageProps,
    ModalRoot,
    ModalStackProvider,
    putConfig,
} from '@inertiaui/modal-react';
import { siteTitle } from '@js/settings';
import { createRoot } from 'react-dom/client';
import {
    discoverModuleSetups,
    executeAfterMountCallbacks,
    executeModuleSetups,
} from './lib/moduleSetup';
import { resolveModularPageComponent } from './lib/utils';

initializeTheme();

const moduleSetups = discoverModuleSetups();

// A native `<dialog>` sits in the top layer, where it would cover the app-level
// confirm dialog and make it unclickable from inside a modal.
putConfig('useNativeDialog', false);

// The package defaults the panel to a hardcoded `bg-white`, which breaks dark
// mode. Set key by key so its other defaults stay in place.
putConfig(
    'modal.panelClasses',
    'bg-background overflow-hidden rounded-lg border shadow-lg',
);

createInertiaApp({
    title: siteTitle,
    resolve: resolveModularPageComponent,
    setup({ el, App: InertiaApp, props }) {
        const locale = (props.initialPage.props?.locale as string) || 'en';

        // What `renderApp()` does, minus its fixed child renderer: the stack
        // provider and `<ModalRoot />` are all the package needs, and this keeps
        // the app's own global-component wrappers in place.
        initFromPageProps({
            // Inertia types the version as nullable, the package as optional.
            initialPage: { version: props.initialPage.version ?? undefined },
        });

        executeModuleSetups(moduleSetups)
            .then(() => {
                createRoot(el).render(
                    <I18nProvider initialLocale={locale}>
                        <ModalStackProvider>
                            <App>
                                <InertiaApp {...props}>
                                    {({ Component, props: pageProps, key }) => (
                                        <>
                                            {getGlobalComponents('top').map(
                                                (TopComponent, i) => (
                                                    <TopComponent key={i} />
                                                ),
                                            )}
                                            <Component
                                                key={key}
                                                {...pageProps}
                                            />
                                            {getGlobalComponents('bottom').map(
                                                (BottomComponent, i) => (
                                                    <BottomComponent key={i} />
                                                ),
                                            )}
                                            <ModalRoot />
                                        </>
                                    )}
                                </InertiaApp>
                            </App>
                        </ModalStackProvider>
                    </I18nProvider>,
                );

                return executeAfterMountCallbacks(moduleSetups);
            })
            .catch(console.error);
    },
    progress: {
        color:
            getComputedStyle(document.documentElement)
                .getPropertyValue('--primary')
                .trim() || '#4B5563',
    },
});
