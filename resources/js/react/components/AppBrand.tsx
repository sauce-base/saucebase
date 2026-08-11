import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useSettings } from '@/hooks/useSettings';
import { Link } from '@inertiajs/react';
import AppLogo from './AppLogo';

/**
 * The application's own mark at the top of the sidebar.
 *
 * What fills the `sidebar-brand` slot when no module claims it. A module with something
 * better to put there registers over it, so core never needs to know whether any
 * particular module is installed.
 */
export default function AppBrand() {
    const settings = useSettings();

    return (
        <SidebarMenu data-testid="app-brand">
            <SidebarMenuItem>
                <SidebarMenuButton size="lg" asChild>
                    <Link href="/dashboard">
                        <div className="text-sidebar-primary-foreground flex size-8 items-center justify-center rounded-lg p-0">
                            <AppLogo size="sm" />
                        </div>
                        <div className="grid flex-1 text-left text-sm leading-tight">
                            <span
                                className="truncate font-medium"
                                data-testid="app-brand-name"
                            >
                                {settings.general.site_name}
                            </span>
                        </div>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
