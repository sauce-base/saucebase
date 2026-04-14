import type { Component } from 'vue';

const top: Component[] = [];
const bottom: Component[] = [];

export function registerGlobalComponent(
    position: 'top' | 'bottom',
    component: Component,
): void {
    (position === 'top' ? top : bottom).push(component);
}

export function getGlobalComponents(position: 'top' | 'bottom'): Component[] {
    return position === 'top' ? top : bottom;
}
