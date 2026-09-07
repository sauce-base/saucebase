import { useLocalization } from '@/composables/useLocalization';
import { createInertiaApp } from '@inertiajs/vue3';
import { putConfig, withInertiaModal } from '@inertiaui/modal-vue';
import { siteTitle } from '@js/settings';
import { useColorMode } from '@vueuse/core';
import { i18nVue, loadLanguageAsync } from 'laravel-vue-i18n';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { resolveLanguage, resolveModularPageComponent } from './lib/utils';

import {
    discoverModuleSetups,
    executeAfterMountCallbacks,
    executeModuleSetups,
} from './lib/moduleSetup';

import '@css/app.css';

/**
 * Used as a wrapper to global components
 */
import AppWrapper from '@/components/App.vue';

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
    setup({ el, App, props, plugin }) {
        const { language } = useLocalization();
        const app = createApp({
            render: () => h(AppWrapper, {}, () => h(App, props)),
        })
            .use(plugin)
            .use(ZiggyVue)
            .use(i18nVue, {
                lang: language.value,
                resolve: resolveLanguage,
            });

        // Wrap the root render in ModalRoot so routes can be opened in a modal.
        // Modal pages resolve through the app's own component resolver, so
        // module pages work without extra registration.
        // Cast: the package types the root render more narrowly than Vue's
        // `App`, which declares it as a plain `Function`.
        withInertiaModal(
            app as unknown as Parameters<typeof withInertiaModal>[0],
        );

        // Execute module setup functions and collect afterMount callbacks
        executeModuleSetups(app, moduleSetups)
            .then(async (afterMountCallbacks) => {
                // Initialize global theme persistence after mount for proper Vue reactivity
                useColorMode({ storageKey: 'appearance' });

                // Wait for translations to be applied before mounting to avoid
                // a flash of untranslated keys on first render (issue: laravel-vue-i18n#189)
                await loadLanguageAsync(language.value);

                // Mount the app
                app.mount(el);

                // Execute module afterMount callbacks
                return executeAfterMountCallbacks(afterMountCallbacks, app);
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
