import { createContext, useContext, type ReactNode } from 'react';

/**
 * Where dialogs and menus should portal to.
 *
 * They default to `document.body`, which is right almost everywhere. Inside the
 * settings modal it is not: the modal keeps focus inside itself with a
 * document-level `focusin` listener, and an overlay portalled to the body sits
 * outside that subtree. The two focus traps then pull against each other on
 * every focus change — deep enough to overflow the stack.
 *
 * Portalling into the modal instead makes the overlay part of what the modal
 * already considers "inside", so only the overlay's own trap is doing anything.
 */
const OverlayContainerContext = createContext<HTMLElement | null>(null);

export function OverlayContainerProvider({
    container,
    children,
}: {
    container: HTMLElement | null;
    children: ReactNode;
}) {
    return (
        <OverlayContainerContext value={container}>
            {children}
        </OverlayContainerContext>
    );
}

/** `null` means the caller is not inside one, and Radix falls back to the body. */
export function useOverlayContainer(): HTMLElement | null {
    return useContext(OverlayContainerContext);
}
