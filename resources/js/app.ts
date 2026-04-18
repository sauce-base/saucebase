import { useLocalization } from '@/composables/useLocalization';
import { createInertiaApp } from '@inertiajs/vue3';
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

import '../css/app.css';

/**
 * Used as a wrapper to global components
 */
import AppWrapper from '@/components/App.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Saucebase';
const moduleSetups = discoverModuleSetups();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
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

        // Execute module setup functions and collect afterMount callbacks
        executeModuleSetups(app, moduleSetups).then(async (afterMountCallbacks) => {
            // Initialize global theme persistence after mount for proper Vue reactivity
            useColorMode();

            // Wait for translations to be applied before mounting to avoid
            // a flash of untranslated keys on first render (issue: laravel-vue-i18n#189)
            await loadLanguageAsync(language.value);

            // Mount the app
            app.mount(el);

            // Execute module afterMount callbacks
            executeAfterMountCallbacks(afterMountCallbacks, app);
        });
    },
    progress: {
        color:
            getComputedStyle(document.documentElement)
                .getPropertyValue('--primary')
                .trim() || '#4B5563',
    },
});
