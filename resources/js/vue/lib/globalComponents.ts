import type { Component } from 'vue';

/**
 * Where a module may hang a component without core knowing the module exists.
 *
 * `top` and `bottom` wrap the page, above and below everything a layout renders.
 * `sidebar-brand` replaces the block above the sidebar navigation, whose default is
 * core's own `AppBrand`.
 *
 * Adding a slot means adding a case here and rendering it in the layout that owns that
 * region. Modules register from their `app.ts`, which `module-loader.js` runs on install,
 * so an adopter wires nothing by hand.
 */
export type GlobalComponentSlot = 'top' | 'bottom' | 'sidebar-brand';

const slots: Record<GlobalComponentSlot, Component[]> = {
    top: [],
    bottom: [],
    'sidebar-brand': [],
};

export function registerGlobalComponent(
    slot: GlobalComponentSlot,
    component: Component,
): void {
    slots[slot].push(component);
}

export function getGlobalComponents(slot: GlobalComponentSlot): Component[] {
    return slots[slot];
}

/**
 * Whether anything has claimed a slot.
 *
 * Lets a layout fall back to its own default rather than rendering an empty region.
 */
export function hasGlobalComponent(slot: GlobalComponentSlot): boolean {
    return slots[slot].length > 0;
}
