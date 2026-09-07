import type { Toast } from '@/types';
import { router } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import DynamicDialog from './DynamicDialog';
import SettingsModal from './SettingsModal';
import { Toaster } from './ui/sonner';

function fireToast(toastProp: Toast) {
    if (!toastProp?.message) return;

    const options = {
        description: toastProp.description,
        duration: toastProp.duration,
        position: toastProp.position,
    };

    switch (toastProp.type) {
        case 'success':
            toast.success(toastProp.message, options);
            break;
        case 'error':
            toast.error(toastProp.message, options);
            break;
        case 'info':
            toast.info(toastProp.message, options);
            break;
        case 'warning':
            toast.warning(toastProp.message, options);
            break;
        case 'loading':
            toast.loading(toastProp.message, options);
            break;
        default:
            toast(toastProp.message, options);
    }
}

interface AppProps {
    children: ReactNode;
}

export default function App({ children }: AppProps) {
    useEffect(() => {
        return router.on('navigate', (event) => {
            const toastProp = event.detail.page.props.toast as
                | Toast
                | undefined;
            if (toastProp) fireToast(toastProp);
        });
    }, []);

    return (
        <>
            <Toaster />
            <DynamicDialog />
            <SettingsModal />
            {children}
        </>
    );
}
