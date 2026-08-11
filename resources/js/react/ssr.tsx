import App from '@/components/App';
import { I18nProvider } from '@/i18n';
import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { siteTitle } from '@js/settings';
import ReactDOMServer from 'react-dom/server';
import { resolveModularPageComponent } from './lib/utils';

createServer((page) => {
    return createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: siteTitle,
        resolve: resolveModularPageComponent,
        setup({
            App: InertiaApp,
            props,
        }: {
            App: React.ComponentType<any>;
            props: Record<string, any>;
        }) {
            const locale = (page.props?.locale as string) || 'en';

            return (
                <I18nProvider initialLocale={locale}>
                    <App>
                        <InertiaApp {...props} />
                    </App>
                </I18nProvider>
            );
        },
    });
});
