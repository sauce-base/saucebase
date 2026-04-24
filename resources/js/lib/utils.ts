import { type ClassValue, clsx } from 'clsx';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { twMerge } from 'tailwind-merge';
import { DefineComponent } from 'vue';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

/**
 * Resolve a Vue component, supporting modular namespaces.
 *
 * @param name The name of the component to resolve. Can include module namespace like 'ModuleName::ComponentPath'.
 * @returns A Promise that resolves to the Vue component.
 */
export const resolveModularPageComponent = (name: string) => {
    if (name.includes('::')) {
        const [moduleName, componentPath] = name.split('::', 2);

        const moduleFolderName = moduleName.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
        const moduleComponentPath = `../../../modules/${moduleFolderName}/resources/js/pages/${componentPath}.vue`;

        const moduleGlobs = import.meta.glob<DefineComponent>(
            '../../../modules/*/resources/js/**/*.vue',
        );

        return resolvePageComponent(moduleComponentPath, moduleGlobs);
    }

    return resolvePageComponent(
        `../pages/${name}.vue`,
        import.meta.glob<DefineComponent>('../pages/**/*.vue'),
    );
};

const langGlobs = import.meta.glob('../../../lang/*.json', {
    eager: true,
}) as Record<string, { default: any }>;

/**
 * Resolve and merge JSON + PHP language files for i18n.
 *
 * Always merges `php_{lang}.json` into `{lang}.json` directly, rather than
 * relying on laravel-vue-i18n's hasPhpTranslations detection (which can fail
 * in newer Vite versions where process.env is not available in browser bundles).
 * PHP translations take precedence over JSON translations.
 */
const resolveLang = (lang: string): Record<string, any> => {
    const jsonData = langGlobs[`../../../lang/${lang}.json`]?.default ?? {};
    const phpData = langGlobs[`../../../lang/php_${lang}.json`]?.default ?? {};
    return { ...jsonData, ...phpData };
};

/**
 * Resolve and load a language JSON file for i18n.
 *
 * @param lang The language code to resolve (e.g., 'en', 'fr').
 * @returns  The language JSON object.
 */
export const resolveLanguage = (lang: string) => resolveLang(lang);

/**
 * Resolve and load a language JSON file for i18n SSR.
 *
 * @param lang The language code to resolve (e.g., 'en', 'fr').
 * @returns  The language JSON object.
 */
export const resolveLanguageForSsr = (lang: string) => resolveLang(lang);
