import LanguageSelector from '@/components/LanguageSelector';
import ThemeSelector from '@/components/ThemeSelector';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useSettings } from '@/hooks/useSettings';
import { settingsHref } from '@/hooks/useSettingsModal';
import { useT } from '@/i18n';
import { handleAction } from '@/lib/navigation';
import type { User } from '@/types';
import type { MenuItem } from '@/types/navigation';
import { Link } from '@inertiajs/react';
import { ChevronsUpDown, UserCircle } from 'lucide-react';
import NavIcon from './NavIcon';

interface NavUserProps {
    user: User;
    items: MenuItem[];
}

export default function NavUser({ user, items }: NavUserProps) {
    const { isMobile } = useSidebar();
    const t = useT();
    const settings = useSettings();

    /**
     * Every section is contributed by a module, so an installation with none has
     * an empty settings modal and no reason to offer the entry. The `settings`
     * route itself is core and always exists, so its presence proves nothing.
     */
    const hasSettingsSections = (settings.sections?.length ?? 0) > 0;

    const userInitials = user.name
        .split(' ')
        .map((word) => word.charAt(0).toUpperCase())
        .slice(0, 2)
        .join('');

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <DropdownMenu modal={false}>
                    <DropdownMenuTrigger asChild>
                        <SidebarMenuButton
                            data-testid="user-menu-trigger"
                            size="lg"
                            className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                        >
                            <Avatar className="h-8 w-8 rounded-lg">
                                <AvatarImage
                                    src={user.avatar}
                                    alt={user.name}
                                />
                                <AvatarFallback className="rounded-lg">
                                    {userInitials}
                                </AvatarFallback>
                            </Avatar>
                            <div className="grid flex-1 text-left text-sm leading-tight">
                                <span className="truncate font-medium">
                                    {user.name}
                                </span>
                                <span className="truncate text-xs">
                                    {user.email}
                                </span>
                            </div>
                            <ChevronsUpDown className="ml-auto size-4" />
                        </SidebarMenuButton>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        side={isMobile ? 'bottom' : 'right'}
                        align="end"
                        sideOffset={4}
                    >
                        <DropdownMenuLabel className="p-0 font-normal">
                            <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <Avatar className="h-8 w-8 rounded-lg">
                                    <AvatarImage
                                        src={user.avatar}
                                        alt={user.name}
                                    />
                                    <AvatarFallback className="rounded-lg">
                                        {userInitials}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="grid flex-1 text-left text-sm leading-tight">
                                    <span className="truncate font-semibold">
                                        {user.name}
                                    </span>
                                    <span className="truncate text-xs">
                                        {user.email}
                                    </span>
                                </div>
                            </div>
                        </DropdownMenuLabel>

                        {hasSettingsSections && (
                            <>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem asChild>
                                    {/* A fragment, so settings open over the current page. */}
                                    <a
                                        href={settingsHref('profile')}
                                        data-testid="open-settings"
                                    >
                                        <UserCircle className="size-4" />
                                        {t('Profile')}
                                    </a>
                                </DropdownMenuItem>
                            </>
                        )}

                        <DropdownMenuSeparator />
                        <DropdownMenuGroup>
                            <LanguageSelector mode="submenu" />
                            <ThemeSelector mode="submenu" />
                        </DropdownMenuGroup>

                        {items.length > 0 && (
                            <>
                                <DropdownMenuSeparator />
                                <DropdownMenuGroup>
                                    {items.map((item) => (
                                        <DropdownMenuItem
                                            key={item.id ?? item.title}
                                            asChild={!item.action}
                                            data-testid={
                                                item.action
                                                    ? `nav-action-${item.action}`
                                                    : undefined
                                            }
                                            onClick={
                                                item.action
                                                    ? (e) =>
                                                          handleAction(
                                                              item.action!,
                                                              e as unknown as MouseEvent,
                                                          )
                                                    : undefined
                                            }
                                        >
                                            {item.action ? (
                                                <>
                                                    <NavIcon icon={item.icon} />
                                                    <span>{t(item.title)}</span>
                                                </>
                                            ) : (
                                                <Link href={item.url ?? '#'}>
                                                    <NavIcon icon={item.icon} />
                                                    <span>{t(item.title)}</span>
                                                </Link>
                                            )}
                                        </DropdownMenuItem>
                                    ))}
                                </DropdownMenuGroup>
                            </>
                        )}
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
