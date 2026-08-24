import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useLocalization } from '@/hooks/useLocalization';
import { useT } from '@/i18n';
import { Globe } from 'lucide-react';
import type { JSX, SVGProps } from 'react';
import IconBR from '~icons/circle-flags/br';
import IconEN from '~icons/circle-flags/en';

type IconComponent = (props: SVGProps<SVGSVGElement>) => JSX.Element;

const iconMap: Record<string, IconComponent> = {
    en: IconEN,
    pt_BR: IconBR,
};

interface LanguageSelectorProps {
    mode?: 'standalone' | 'submenu';
    triggerClass?: string;
}

export default function LanguageSelector({
    mode = 'standalone',
    triggerClass = 'flex items-center rounded-lg p-2 text-muted-foreground transition-colors duration-200 hover:bg-accent hover:text-accent-foreground',
}: LanguageSelectorProps) {
    const t = useT();
    const { language, locales, setLanguage } = useLocalization();

    const languages = Object.entries(locales).map(([code, name]) => ({
        code,
        name: name as string,
        Icon: iconMap[code] ?? null,
    }));

    // Nothing to choose between is nothing to show.
    if (languages.length <= 1) {
        return null;
    }

    const currentLanguage =
        languages.find((l) => l.code === language) ?? languages[0];
    const CurrentIcon = currentLanguage?.Icon ?? null;

    if (mode === 'standalone') {
        return (
            <DropdownMenu modal={false}>
                <DropdownMenuTrigger asChild>
                    <button
                        className={triggerClass}
                        aria-label={t('Language Selector')}
                    >
                        {CurrentIcon ? (
                            <CurrentIcon className="size-4.5 rounded-full" />
                        ) : (
                            <Globe className="size-4.5 rounded-full" />
                        )}
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="min-w-40">
                    {languages.map(({ code, name, Icon }) => (
                        <DropdownMenuItem
                            key={code}
                            onClick={() => setLanguage(code)}
                            className={
                                language === code
                                    ? 'bg-accent text-accent-foreground'
                                    : ''
                            }
                        >
                            {Icon ? (
                                <Icon className="size-4 rounded-full" />
                            ) : (
                                <Globe className="size-4 rounded-full" />
                            )}
                            {name}
                        </DropdownMenuItem>
                    ))}
                </DropdownMenuContent>
            </DropdownMenu>
        );
    }

    return (
        <DropdownMenuSub>
            <DropdownMenuSubTrigger
                data-testid="language-selector-trigger"
                className="[&>svg]:text-muted-foreground [&>svg]:mr-2"
            >
                <Globe className="size-3.5 rounded-full" />
                {t('Language')}
            </DropdownMenuSubTrigger>
            <DropdownMenuSubContent>
                {languages.map(({ code, name, Icon }) => (
                    <DropdownMenuItem
                        key={code}
                        onClick={() => setLanguage(code)}
                        className={language === code ? 'bg-accent' : ''}
                    >
                        {Icon ? (
                            <Icon className="h-4 w-4 rounded-full" />
                        ) : (
                            <Globe className="h-4 w-4 rounded-full" />
                        )}
                        {name}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuSubContent>
        </DropdownMenuSub>
    );
}
