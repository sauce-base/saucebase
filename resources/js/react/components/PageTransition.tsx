import { usePage } from '@inertiajs/react';
import { type ReactNode, useEffect, useRef, useState } from 'react';

const DURATION = 300;

export default function PageTransition({ children }: { children: ReactNode }) {
    const page = usePage();
    const [visible, setVisible] = useState(true);
    const [displayedChildren, setDisplayedChildren] = useState(children);
    const prevUrl = useRef(page.url);
    // Always holds the latest children without being a useEffect dependency
    const pendingChildren = useRef(children);
    pendingChildren.current = children;

    useEffect(() => {
        if (page.url === prevUrl.current) {
            setDisplayedChildren(children);

            return;
        }

        prevUrl.current = page.url;

        const prefersReducedMotion =
            typeof window !== 'undefined' &&
            window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (prefersReducedMotion) {
            setDisplayedChildren(pendingChildren.current);
            return;
        }

        setVisible(false);
        const t = setTimeout(() => {
            setDisplayedChildren(pendingChildren.current);
            setVisible(true);
        }, DURATION);
        return () => clearTimeout(t);
    }, [children, page.url]);

    return (
        <div
            className={`h-full w-full motion-safe:transition-opacity motion-safe:duration-300 motion-safe:ease-in-out ${visible ? 'opacity-100' : 'opacity-0'}`}
        >
            {displayedChildren}
        </div>
    );
}
