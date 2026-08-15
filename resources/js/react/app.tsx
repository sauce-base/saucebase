import App from '@/components/App';
import { initializeTheme } from '@/hooks/useTheme';
import { I18nProvider } from '@/i18n';
import { getGlobalComponents } from '@/lib/globalComponents';
import '@css/app.css';
import { createInertiaApp } from '@inertiajs/react';
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

createInertiaApp({
    title: siteTitle,
    resolve: resolveModularPageComponent,
    setup({ el, App: InertiaApp, props }) {
        const locale = (props.initialPage.props?.locale as string) || 'en';

        executeModuleSetups(moduleSetups).then(() => {
            createRoot(el).render(
                <I18nProvider initialLocale={locale}>
                    <App>
                        <InertiaApp {...props}>
                            {({ Component, props: pageProps, key }) => (
                                <>
                                    {getGlobalComponents('top').map(
                                        (TopComponent, i) => (
                                            <TopComponent key={i} />
                                        ),
                                    )}
                                    <Component key={key} {...pageProps} />
                                    {getGlobalComponents('bottom').map(
                                        (BottomComponent, i) => (
                                            <BottomComponent key={i} />
                                        ),
                                    )}
                                </>
                            )}
                        </InertiaApp>
                    </App>
                </I18nProvider>,
            );

            executeAfterMountCallbacks(moduleSetups);
        });
    },
    progress: {
        color:
            getComputedStyle(document.documentElement)
                .getPropertyValue('--primary')
                .trim() || '#4B5563',
    },
});
