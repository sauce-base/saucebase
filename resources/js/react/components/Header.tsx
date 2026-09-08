import { useModules } from '@/hooks/useModules';
import { useT } from '@/i18n';
import { cn } from '@/lib/utils';
import type { MenuItem } from '@/types/navigation';
import { Link, usePage } from '@inertiajs/react';
import { ModalLink } from '@inertiaui/modal-react';
import { ArrowRight, ExternalLink, Menu, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import AppLogo from './AppLogo';
import LanguageSelector from './LanguageSelector';
import ThemeSelector from './ThemeSelector';

export default function Header() {
    const t = useT();
    const page = usePage();
    const { has } = useModules();
    const [isScrolled, setIsScrolled] = useState(false);
    const [isVisible, setIsVisible] = useState(true);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const headerRef = useRef<HTMLElement>(null);
    const lastScrollY = useRef(0);

    const landingNav = ((page.props.navigation as Record<string, unknown>)
        ?.landing ?? []) as MenuItem[];
    const auth = page.props.auth as
        | {
              user?: unknown;
              registration_enabled?: boolean;
              modal_enabled?: boolean;
          }
        | undefined;
    const isLoggedIn = auth?.user != null;
    const isGuest = has('auth') && !isLoggedIn;
    const canRegister = isGuest && auth?.registration_enabled === true;

    /**
     * How the sign-in and registration entry points render.
     *
     * A modal over the current page when the site has that switched on, an
     * ordinary page link when it has not. `navigate` puts the auth route in the
     * address bar while the modal is open, so Back closes it and a refresh or
     * shared link lands on the full page.
     */
    const AuthLink = auth?.modal_enabled ? ModalLink : Link;
    const authLinkProps = auth?.modal_enabled ? { navigate: true } : {};

    useEffect(() => {
        const handleScroll = () => {
            // Locking body scroll (e.g. ModuleModal) collapses the scrollable
            // height, clamping window.scrollY to 0 and firing a spurious scroll
            // event — ignore it so the header doesn't slide back into view.
            if (document.body.style.overflow === 'hidden') return;

            const currentScrollY = window.scrollY;
            const headerHeight = headerRef.current?.offsetHeight ?? 80;

            setIsScrolled(currentScrollY > 10);

            if (currentScrollY < headerHeight) {
                setIsVisible(true);
            } else if (currentScrollY < lastScrollY.current) {
                setIsVisible(true);
            } else if (currentScrollY > lastScrollY.current) {
                setIsVisible(false);
                setMobileMenuOpen(false);
            }

            lastScrollY.current = currentScrollY;
        };

        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    return (
        <header
            ref={headerRef}
            className={`fixed top-0 right-0 left-0 z-50 transition-all duration-300 ${isScrolled ? 'dark:border-b-border/25 border-b bg-white/5 shadow-2xl backdrop-blur-lg' : 'bg-transparent'} ${isVisible ? 'translate-y-0' : '-translate-y-full'}`}
        >
            <nav className="mx-auto max-w-7xl px-6 py-3">
                <div className="flex items-center justify-between">
                    {/* Logo */}
                    <Link
                        href="/"
                        className="flex shrink-0 items-center transition-opacity hover:opacity-80"
                    >
                        <AppLogo size="md" showText />
                    </Link>

                    {/* Centered navigation */}
                    <div className="absolute left-1/2 hidden -translate-x-1/2 items-center space-x-1 lg:flex">
                        {landingNav.map((item) => (
                            <a
                                key={item.slug}
                                href={item.url}
                                target={item.newPage ? '_blank' : '_self'}
                                className={cn(
                                    'after:bg-primary text-muted-foreground hover:text-foreground relative px-4 py-2 text-sm font-semibold transition-all duration-300 after:absolute after:bottom-0 after:left-1/2 after:h-0.5 after:w-0 after:-translate-x-1/2 after:rounded-xl after:transition-all after:duration-300 hover:after:w-3/4',
                                    item.class,
                                )}
                            >
                                {t(item.title)}
                                {item.newPage && (
                                    <ExternalLink className="-mt-1 ml-1 inline-block size-3.5" />
                                )}
                            </a>
                        ))}
                    </div>

                    {/* Right side */}
                    <div className="hidden items-center space-x-3 lg:flex">
                        <div className="flex items-center space-x-1">
                            <LanguageSelector mode="standalone" />
                            <ThemeSelector mode="standalone" />
                        </div>
                        {isGuest && (
                            <>
                                <AuthLink
                                    {...authLinkProps}
                                    href={route('login')}
                                    className="text-muted-foreground hover:bg-accent hover:text-accent-foreground cursor-pointer rounded-xl px-4 py-2 text-sm font-medium transition-all duration-200"
                                    data-testid="header-sign-in"
                                >
                                    {t('Sign In')}
                                </AuthLink>
                                {canRegister && (
                                    <AuthLink
                                        {...authLinkProps}
                                        href={route('register')}
                                        className="bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary inline-flex cursor-pointer items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold transition-all duration-200 focus:ring-2 focus:ring-offset-2 focus:outline-none"
                                        data-testid="header-get-started"
                                    >
                                        {t('Get Started')}
                                    </AuthLink>
                                )}
                            </>
                        )}
                        {route().has('dashboard') && isLoggedIn && (
                            <Link
                                href={route('dashboard')}
                                className="bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold transition-all duration-200"
                            >
                                {t('Dashboard')}
                            </Link>
                        )}
                        {route().has('logout') && isLoggedIn && (
                            <Link
                                href={route('logout')}
                                className="text-muted-foreground hover:bg-accent hover:text-accent-foreground rounded-xl px-4 py-2 text-sm font-medium transition-all duration-200"
                            >
                                {t('Logout')}
                            </Link>
                        )}
                    </div>

                    {/* Mobile menu button */}
                    <div className="flex items-center space-x-3 lg:hidden">
                        <LanguageSelector mode="standalone" />
                        <ThemeSelector mode="standalone" />
                        <button
                            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                            aria-label={
                                mobileMenuOpen
                                    ? t('Close mobile menu')
                                    : t('Open mobile menu')
                            }
                            aria-expanded={mobileMenuOpen}
                            className="text-muted-foreground hover:bg-accent hover:text-accent-foreground rounded-xl p-2 transition-colors duration-200"
                        >
                            {mobileMenuOpen ? (
                                <X className="h-6 w-6" />
                            ) : (
                                <Menu className="h-6 w-6" />
                            )}
                        </button>
                    </div>
                </div>

                {/* Mobile menu */}
                {mobileMenuOpen && (
                    <div className="border-border/40 bg-background/80 mx-2 mt-4 rounded-lg border-t pb-6 backdrop-blur-sm lg:hidden">
                        <div className="flex flex-col space-y-1 px-2 pt-4">
                            {landingNav.map((item) => (
                                <a
                                    key={item.slug}
                                    href={item.url}
                                    target={item.newPage ? '_blank' : '_self'}
                                    className={cn(
                                        'after:bg-primary hover:text-primary text-foreground relative px-4 py-3 text-base font-semibold transition-all duration-300 after:absolute after:bottom-1 after:left-4 after:h-0.5 after:w-0 after:rounded-xl after:transition-all after:duration-300 hover:after:w-1/2',
                                        item.class,
                                    )}
                                    onClick={() => setMobileMenuOpen(false)}
                                >
                                    {t(item.title)}
                                </a>
                            ))}

                            <div className="border-border/60 mt-2 border-t pt-4">
                                {isGuest && (
                                    <div className="flex gap-3">
                                        <AuthLink
                                            {...authLinkProps}
                                            href={route('login')}
                                            className="border-border text-foreground hover:bg-accent flex-1 cursor-pointer rounded-xl border px-4 py-2.5 text-center text-sm font-medium transition-all duration-200"
                                            data-testid="header-sign-in-mobile"
                                            onClick={() =>
                                                setMobileMenuOpen(false)
                                            }
                                        >
                                            {t('Sign In')}
                                        </AuthLink>
                                        {canRegister && (
                                            <AuthLink
                                                {...authLinkProps}
                                                href={route('register')}
                                                className="bg-primary text-primary-foreground hover:bg-primary/90 flex flex-1 cursor-pointer items-center justify-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all duration-200"
                                                data-testid="header-get-started-mobile"
                                                onClick={() =>
                                                    setMobileMenuOpen(false)
                                                }
                                            >
                                                {t('Get Started')}
                                                <ArrowRight className="h-3.5 w-3.5" />
                                            </AuthLink>
                                        )}
                                    </div>
                                )}
                                {isLoggedIn && (
                                    <Link
                                        href={route('dashboard')}
                                        className="bg-primary text-primary-foreground hover:bg-primary/90 flex w-full items-center justify-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all duration-200"
                                        onClick={() => setMobileMenuOpen(false)}
                                    >
                                        {t('Dashboard')}
                                        <ArrowRight className="h-3.5 w-3.5" />
                                    </Link>
                                )}
                            </div>
                        </div>
                    </div>
                )}
            </nav>
        </header>
    );
}
