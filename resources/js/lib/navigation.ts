import { useColorMode } from '@vueuse/core';
import type { Component } from 'vue';

// ── Action registry ───────────────────────────────────────────────────────

/**
 * Action handler function type.
 */
export type ActionHandler = (event: MouseEvent) => void | Promise<void>;

/**
 * Registry of action handlers.
 *
 * Maps action IDs to frontend handler functions.
 * Modules can register their own handlers using registerAction().
 */
const actionRegistry: Record<string, ActionHandler> = {
    /**
     * Theme toggle action.
     */
    'ui.theme.toggle': (event: MouseEvent) => {
        event.preventDefault();

        const colorMode = useColorMode();
        colorMode.value = colorMode.value === 'dark' ? 'light' : 'dark';
    },

    /**
     * Language selector action.
     * TODO: Implement language selection logic
     */
    'ui.language.select': (event: MouseEvent) => {
        event.preventDefault();
        console.warn('Language selection not yet implemented');
    },
};

/**
 * Handle a menu action.
 *
 * @param actionId - The action ID (from MenuItem.meta.action)
 * @param event - The mouse click event
 */
export function handleAction(actionId: string, event: MouseEvent): void {
    const handler = actionRegistry[actionId];

    if (handler) {
        handler(event);
    } else {
        console.warn(`No handler registered for action: ${actionId}`);
    }
}

/**
 * Register a custom action handler.
 *
 * Allows modules to register additional action handlers at runtime.
 *
 * @param actionId - The action ID
 * @param handler - The handler function
 */
export function registerAction(actionId: string, handler: ActionHandler): void {
    if (actionRegistry[actionId]) {
        console.warn(`Action handler already registered for: ${actionId}`);
    }

    actionRegistry[actionId] = handler;
}

// ── Icon registry ─────────────────────────────────────────────────────────

import IconHelpCircle from '~icons/lucide/help-circle';
import IconShieldCheck from '~icons/lucide/shield-check';
import IconSquareTerminal from '~icons/lucide/square-terminal';
import IconGithub from '~icons/mdi/github';

const iconRegistry: Record<string, Component> = {
    dashboard: IconSquareTerminal,
    github: IconGithub,
    admin: IconShieldCheck,
    documentation: IconHelpCircle,
};

/**
 * Register a navigation icon.
 *
 * Modules call this in their app.ts setup() to register icon components
 * for their navigation items.
 *
 * @param name - The icon identifier (matches the `icon` attribute in navigation.php)
 * @param component - The Vue icon component
 */
export function registerIcon(name: string, component: Component): void {
    iconRegistry[name] = component;
}

/**
 * Resolve a registered icon component by name.
 *
 * @param name - The icon identifier
 * @returns The Vue component or undefined if not registered
 */
export function resolveIcon(name: string): Component | undefined {
    return iconRegistry[name];
}
