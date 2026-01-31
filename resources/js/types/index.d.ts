export interface User {
    id: number;
    name: string;
    email: string;
    avatar: string;
    last_login_at: string | null;
    role?: string;
}

export interface Breadcrumb {
    title: string;
    url?: string;
    active?: boolean;
    attributes?: {
        label?: string;
        [key: string]: any;
    };
    children?: any[];
    depth?: number;
}

export interface Toast {
    message: string;
    type: 'default' | 'success' | 'error' | 'info' | 'warning' | 'loading';
    position?:
        | 'top-left'
        | 'top-right'
        | 'bottom-left'
        | 'bottom-right'
        | 'top-center'
        | 'bottom-center';
    description?: string;
    action?: {
        label: string;
        type?: 'route' | 'url' | 'external_url' | 'function';
        onClick?: string;
    };
    duration?: number;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    locale?: string;
    locales?: Record<string, string>;
    navigation?: Record<string, any>;
    breadcrumbs?: Breadcrumb[];
    toast?: Toast;
};
