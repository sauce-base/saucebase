<script setup lang="ts">
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useLocalization } from '@/composables/useLocalization';
import { Globe } from 'lucide-vue-next';
import { computed } from 'vue';
import IconBR from '~icons/circle-flags/br';
import IconEN from '~icons/circle-flags/en';

interface Props {
    /**
     * Display mode - 'standalone' for main menu, 'submenu' for nested dropdown
     */
    mode?: 'standalone' | 'submenu';
    /**
     * Custom trigger class for standalone mode
     */
    triggerClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'standalone',
    triggerClass:
        'flex items-center rounded-lg p-2 text-gray-700 transition-colors duration-200 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800',
});

const { language, locales, setLanguage } = useLocalization();

// Icon mapping for different locales
const iconMap: Record<string, any> = {
    en: IconEN,
    pt_BR: IconBR,
};

// Map backend locales to language objects with icons
const languages = computed(() => {
    const localesData = locales.value;
    return Object.entries(localesData).map(([code, name]) => ({
        code,
        name: name as string,
        icon: iconMap[code] || Globe, // Fallback to Globe icon if not found
    }));
});

const switchLanguage = async (langCode: string) => {
    await setLanguage(langCode);
};

const currentLanguage = computed(() => {
    const langs = languages.value;

    if (!langs || langs.length === 0) {
        return { code: 'en', name: 'English', icon: iconMap.en };
    }

    return langs.find((lang) => lang.code === language.value) || langs[0];
});
</script>

<template>
    <!-- Standalone Mode (Landing Page) -->
    <DropdownMenu v-if="mode === 'standalone'">
        <DropdownMenuTrigger as-child>
            <button
                :class="props.triggerClass"
                :aria-label="$t('Language Selector')"
            >
                <slot name="trigger" :current-language="currentLanguage">
                    <component :is="currentLanguage.icon" class="size-4.5" />
                </slot>
            </button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="min-w-40">
            <DropdownMenuItem
                v-for="lang in languages"
                :key="lang.code"
                @click="switchLanguage(lang.code)"
                :class="{
                    'bg-accent text-accent-foreground': language === lang.code,
                }"
            >
                <component :is="lang.icon" class="size-4" />
                {{ lang.name }}
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>

    <!-- Submenu Mode (NavUser) -->
    <DropdownMenuSub v-else>
        <DropdownMenuSubTrigger
            class="[&>svg]:text-muted-foreground [&>svg]:mr-2"
        >
            <slot name="submenu-trigger" :current-language="currentLanguage">
                <Globe class="size-3.5" />
                {{ $t('Language') }}
            </slot>
        </DropdownMenuSubTrigger>
        <DropdownMenuSubContent>
            <DropdownMenuItem
                v-for="lang in languages"
                :key="lang.code"
                @click="switchLanguage(lang.code)"
                :class="{ 'bg-accent': language === lang.code }"
            >
                <component :is="lang.icon" class="h-4 w-4" />
                {{ lang.name }}
            </DropdownMenuItem>
        </DropdownMenuSubContent>
    </DropdownMenuSub>
</template>
