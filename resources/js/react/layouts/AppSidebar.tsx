import AppBrand from '@/components/AppBrand';
import NavGroup from '@/components/ui/navigation/NavGroup';
import NavUser from '@/components/ui/navigation/NavUser';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';
import {
    getGlobalComponents,
    hasGlobalComponent,
} from '@/lib/globalComponents';
import type { PageProps, User } from '@/types';
import type { Navigation } from '@/types/navigation';
import { usePage } from '@inertiajs/react';

export default function AppSidebar() {
    const page =
        usePage<PageProps<{ navigation: Navigation; auth: { user: User } }>>();

    const items = page.props.navigation?.main ?? [];
    const secondaryItems = page.props.navigation?.secondary ?? [];
    const userItems = page.props.navigation?.user ?? [];
    const user = page.props.auth?.user;

    // A module may own this block instead. Registration happens at import time, so this
    // is settled before the first render.
    const brandIsClaimed = hasGlobalComponent('sidebar-brand');

    return (
        <Sidebar variant="inset" collapsible="icon" className="bg-transparent">
            <SidebarHeader data-testid="sidebar-header">
                {brandIsClaimed ? (
                    getGlobalComponents('sidebar-brand').map((C, i) => (
                        <C key={i} />
                    ))
                ) : (
                    <AppBrand />
                )}
            </SidebarHeader>
            <SidebarContent>
                <NavGroup items={items} />
                <NavGroup items={secondaryItems} className="mt-auto" />
            </SidebarContent>
            <SidebarFooter>
                {user && <NavUser user={user} items={userItems} />}
            </SidebarFooter>
        </Sidebar>
    );
}
