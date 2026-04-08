import { useHttp, usePage } from '@inertiajs/vue3';
import { loadLanguageAsync } from 'laravel-vue-i18n';
import { computed, readonly, ref, watch, type Ref } from 'vue';

// Singleton state to ensure all components share the same locale state
let languageState: Ref<string> | null = null;
let isInitialized = false;

/**
 * Composable for managing application localization
 *
 * Provides reactive locale state from Inertia props and handles language switching.
 * The locale is sourced from the server session and automatically syncs across
 * page navigations.
 *
 * Uses singleton pattern to ensure all components share the same state.
 */
export const useLocalization = () => {
    const page = usePage();

    // Initialize singleton state only once
    if (!languageState) {
        languageState = ref<string>((page.props?.locale as string) || 'en');
    }

    // Get available locales mapping from Inertia props
    const locales = computed(
        () => (page.props?.locales as Record<string, string>) || {},
    );

    /**
     * Change the application language
     * Updates both the backend session and frontend i18n
     */
    const setLanguage = async (lang: string) => {
        const { post } = useHttp();
        try {
            // Update backend session
            await post(route('locale', { locale: lang }));

            // Update local state
            if (languageState) {
                languageState.value = lang;
            }

            // Load i18n translations for the new language
            await loadLanguageAsync(lang);
        } catch (error) {
            console.error('Error changing language', error);
            throw error;
        }
    };

    // Set up watch only once to avoid multiple watchers
    if (!isInitialized) {
        isInitialized = true;

        // Sync language when page props change (on navigation)
        watch(
            () => page.props?.locale,
            (newLocale) => {
                if (
                    newLocale &&
                    languageState &&
                    newLocale !== languageState.value
                ) {
                    languageState.value = newLocale as string;
                    loadLanguageAsync(newLocale as string);
                }
            },
        );
    }

    return {
        language: readonly(languageState),
        locales,
        setLanguage,
    };
};
