import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { i18nVue } from 'laravel-vue-i18n';
import { createSSRApp, h } from 'vue';
import { renderToString } from 'vue/server-renderer';
import { ZiggyVue } from 'ziggy-js';
import { resolveLanguage, resolveModularPageComponent } from './lib/utils';

/**
 * Used as a wrapper to global components
 */
import App from '@/components/App.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Sauce Base';

createServer(
    (page) =>
        createInertiaApp({
            page,
            render: renderToString,
            title: (title) => `${title} - ${appName}`,
            resolve: resolveModularPageComponent,
            setup({ App: InertiaApp, props, plugin }) {
                (globalThis as any).Ziggy = page.props.ziggy;

                const app = createSSRApp({
                    render: () => h(App, {}, () => h(InertiaApp, props)),
                })
                    .use(plugin)
                    .use(ZiggyVue, page.props.ziggy as any)
                    .use(i18nVue, {
                        resolve: resolveLanguage,
                    });

                // Note: Module setups are not executed in SSR context
                // They contain browser-specific code (event handlers, DOM interactions)
                // and are only needed in the client-side app.ts

                return app;
            },
            defaults: {
                future: {
                    useScriptElementForInitialPage: true,
                },
            },
        }),
    {
        cluster: true,
    },
);
