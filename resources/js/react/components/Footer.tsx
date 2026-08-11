import { useT } from '@/i18n';
import { useSettings } from '@/hooks/useSettings';
import { Link } from '@inertiajs/react';
import { Heart } from 'lucide-react';

export default function Footer() {
    const t = useT();
    const settings = useSettings();

    return (
        <footer className="relative mt-auto w-full overflow-hidden pb-12 sm:pb-48">
            <div className="relative z-10 mx-4 py-8 min-[450px]:mx-auto min-[450px]:max-w-7xl min-[450px]:px-6">
                <div className="text-muted-foreground flex w-full flex-col items-center justify-between gap-4 text-sm md:flex-row">
                    <div className="flex flex-col items-center gap-y-1 md:flex-row md:items-center md:gap-x-6 md:gap-y-0">
                        <span data-testid="footer-app-name">
                            © {new Date().getFullYear()}{' '}
                            {settings.general.site_name}
                        </span>
                        <a
                            href="https://github.com/saucebase-dev/saucebase"
                            className="hover:text-foreground"
                        >
                            {t('GitHub')}
                        </a>
                        <a
                            href="https://saucebase-dev.github.io/docs/"
                            className="hover:text-foreground"
                        >
                            {t('Documentation')}
                        </a>
                        {route().has('privacy') && (
                            <Link
                                href={route('privacy')}
                                className="hover:text-foreground"
                            >
                                {t('Privacy')}
                            </Link>
                        )}
                        {route().has('terms') && (
                            <Link
                                href={route('terms')}
                                className="hover:text-foreground"
                            >
                                {t('Terms')}
                            </Link>
                        )}
                    </div>
                    <div className="flex items-center gap-1">
                        <span>{t('Crafted with')}</span>
                        <Heart className="h-4 w-4 text-red-500" />
                        <span>{t('for developers')}</span>
                        <span>{t('by :name', { name: 'Renan Roble' })}</span>
                    </div>
                </div>
            </div>
            <div className="absolute bottom-0 z-0 translate-y-[20%] scale-105 px-4 font-mono">
                <p className="text-center text-[21vw] leading-none font-black -tracking-widest select-none">
                    <span className="text-foreground/5">Sauce</span>
                    <span className="text-foreground/10">base</span>
                </p>
            </div>
        </footer>
    );
}
