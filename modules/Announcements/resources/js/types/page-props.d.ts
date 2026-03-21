import type { Announcement } from '.';

declare module '@inertiajs/vue3' {
    interface PageProps {
        announcement?: Announcement | null;
    }
}

export {};
