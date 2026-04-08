import { FIELD_DEFS } from '../fields';
import type { FieldState, Theme } from '../types';

export const THEME_STORAGE_KEY = 'sb-theme-theme';

// Allowlist derived from field definitions — only these vars are written as inline styles.
const MANAGED_VARS_SET = new Set(FIELD_DEFS.flatMap((f) => f.vars));

// Vars from non-color field types (font, unit/radius) are mode-agnostic.
// Everything else in a theme JSON (colors, borders, cards, etc.) varies by mode.
const NON_COLOR_FIELD_VARS = new Set(
    FIELD_DEFS.filter((f) => f.type !== 'color').flatMap((f) => f.vars),
);

const FONT_FIELD_VARS = new Set(
    FIELD_DEFS.filter((f) => f.type === 'font').flatMap((f) => f.vars),
);

function fontFallback(cssVar: string): string {
    if (cssVar.includes('mono')) return 'monospace';
    if (cssVar.includes('serif')) return 'serif';
    return 'sans-serif';
}

export function loadGoogleFont(font: string): void {
    if (typeof document === 'undefined' || !font) {
        return;
    }
    const href = `https://fonts.googleapis.com/css2?family=${encodeURIComponent(font)}:wght@400;500;600;700&display=swap`;
    if (document.querySelector(`link[href="${href}"]`)) {
        return;
    }
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = href;
    document.head.appendChild(link);
}

export function applyFontClass(cssVar: string, font: string): void {
    if (typeof document === 'undefined') return;
    const styleId = `sb-theme-${cssVar.slice(2)}`; // "--font-sans" → "sb-theme-font-sans"
    let el = document.getElementById(styleId) as HTMLStyleElement | null;
    if (!el) {
        el = document.createElement('style');
        el.id = styleId;
        document.head.appendChild(el);
    }
    const cls = cssVar.slice(2); // "--font-sans" → "font-sans"
    el.textContent = font
        ? `.${cls} { font-family: "${font}", ${fontFallback(cssVar)} !important; }`
        : '';
}

export function setProperty(name: string, value: string): void {
    document.documentElement.style.setProperty(name, value);
}

/** Extract font name from a CSS font-family value like '"Nunito", sans-serif' */
export function parseFontName(cssFontFamily: string): string {
    return cssFontFamily.replace(/["']/g, '').split(',')[0].trim();
}

export function applyThemeVars(theme: Theme | null, isDark: boolean): void {
    if (typeof document === 'undefined') {
        return;
    }
    const el = document.documentElement;

    // Clear all inline CSS vars so stylesheet defaults can take over when theme is null
    const toRemove: string[] = [];
    for (let i = 0; i < el.style.length; i++) {
        if (el.style[i].startsWith('--')) {
            toRemove.push(el.style[i]);
        }
    }
    toRemove.forEach((v) => el.style.removeProperty(v));

    if (!theme) {
        return;
    }

    const lightVars = theme.light ?? {};
    const modeVars = isDark ? (theme.dark ?? {}) : lightVars;

    // Apply only managed vars from the current mode — skip anything not in FIELD_DEFS
    Object.entries(modeVars).forEach(([key, value]) => {
        if (!MANAGED_VARS_SET.has(key)) return;
        setProperty(key, value);
        if (FONT_FIELD_VARS.has(key)) {
            const name = parseFontName(value);
            loadGoogleFont(name);
            applyFontClass(key, name);
        }
    });

    // Override mode-agnostic vars (font, radius) with light values so they
    // remain consistent regardless of which mode is active
    Object.entries(lightVars).forEach(([key, value]) => {
        if (!NON_COLOR_FIELD_VARS.has(key)) return;
        setProperty(key, value);
        if (FONT_FIELD_VARS.has(key)) {
            const name = parseFontName(value);
            loadGoogleFont(name);
            applyFontClass(key, name);
        }
    });

    // Compute and apply shadow strings on every theme load / mode switch.
    const shadowColor = modeVars['--shadow-color'] ?? lightVars['--shadow-color'];
    if (shadowColor) {
        const opacity = parseFloat((modeVars['--shadow-opacity'] ?? lightVars['--shadow-opacity']) ?? '0.2');
        const blur    = parseFloat(lightVars['--shadow-blur']    ?? '30');
        const spread  = parseFloat(lightVars['--shadow-spread']  ?? '-10');
        const offsetX = parseFloat(lightVars['--shadow-offset-x'] ?? '0');
        const offsetY = parseFloat(lightVars['--shadow-offset-y'] ?? '1');
        for (const [key, val] of Object.entries(computeShadows(shadowColor, opacity, blur, spread, offsetX, offsetY))) {
            setProperty(key, val);
        }
    }

    // Compute and apply radius + tracking scales so derived vars are always in sync.
    const radiusValue = lightVars['--radius'];
    if (radiusValue) {
        for (const [key, val] of Object.entries(computeRadiusScale(radiusValue))) {
            setProperty(key, val);
        }
    }

    const trackingValue = lightVars['--tracking-normal'];
    if (trackingValue) {
        for (const [key, val] of Object.entries(computeTrackingScale(parseFloat(trackingValue)))) {
            setProperty(key, val);
        }
    }
}

export function applyFieldToDom(field: FieldState, value: string): void {
    if (field.type === 'color') {
        field.vars.forEach((v) => setProperty(v, value));
    } else if (field.type === 'unit') {
        // field.value stores just the number (e.g. "1.225"); add unit before applying
        const unit = field.props?.unit ?? 'rem';
        const valueWithUnit = `${value}${unit}`;
        field.vars.forEach((v) => setProperty(v, valueWithUnit));
    } else if (field.type === 'font') {
        loadGoogleFont(value);
        field.vars.forEach((v) => {
            applyFontClass(v, value);
            setProperty(v, `"${value}", ${fontFallback(v)}`);
        });
    } else if (field.type === 'select') {
        field.vars.forEach((v) => setProperty(v, value));
    }
}

function shadowLayer(
    x: number,
    y: number,
    blur: number,
    spread: number,
    color: string,
    opacity: number,
): string {
    return `${x}px ${y}px ${blur}px ${spread}px color-mix(in srgb, ${color} ${(opacity * 100).toFixed(1)}%, transparent)`;
}

/**
 * Compute all 8 shadow scale values from the 6 component vars.
 * Returns a map of CSS var name → computed box-shadow string.
 */
export function computeShadows(
    color: string,
    opacity: number,
    blur: number,
    spread: number,
    offsetX: number,
    offsetY: number,
): Record<string, string> {
    const l1 = (mult: number) => shadowLayer(offsetX, offsetY, blur, spread, color, opacity * mult);
    const l2 = (y2: number, blur2: number) => shadowLayer(offsetX, y2, blur2, spread - 1, color, opacity);
    const smLayers = `${l1(1)}, ${l2(1, 2)}`;
    return {
        '--shadow-2xs': l1(0.5),
        '--shadow-xs': l1(0.5),
        '--shadow-sm': smLayers,
        '--shadow': smLayers,
        '--shadow-md': `${l1(1)}, ${l2(2, 4)}`,
        '--shadow-lg': `${l1(1)}, ${l2(4, 6)}`,
        '--shadow-xl': `${l1(1)}, ${l2(8, 10)}`,
        '--shadow-2xl': l1(2.5),
    };
}

/**
 * Compute the full radius scale from a single base radius value.
 * Uses linear px offsets: sm=-4, md=-2, lg=0, xl=+4, 2xl=+8, 3xl=+16, 4xl=+24.
 * Returns calc() strings so rem units are preserved (e.g. "calc(0.875rem + 4px)").
 */
export function computeRadiusScale(radiusValue: string): Record<string, string> {
    const steps: [string, number][] = [
        ['--radius-sm', -4], ['--radius-md', -2], ['--radius-lg', 0],
        ['--radius-xl', 4],  ['--radius-2xl', 8], ['--radius-3xl', 16], ['--radius-4xl', 24],
    ];
    return Object.fromEntries(
        steps.map(([key, px]) =>
            px === 0
                ? [key, radiusValue]
                : [key, `calc(${radiusValue} ${px > 0 ? '+' : '-'} ${Math.abs(px)}px)`],
        ),
    );
}

/**
 * Compute the full tracking (letter-spacing) scale from a base value in em.
 */
export function computeTrackingScale(base: number): Record<string, string> {
    return {
        '--tracking-tighter': `${(base - 0.05).toFixed(3)}em`,
        '--tracking-tight':   `${(base - 0.025).toFixed(3)}em`,
        '--tracking-normal':  `${base}em`,
        '--tracking-wide':    `${(base + 0.025).toFixed(3)}em`,
        '--tracking-wider':   `${(base + 0.05).toFixed(3)}em`,
        '--tracking-widest':  `${(base + 0.1).toFixed(3)}em`,
    };
}

export function clearThemeOverrides(): void {
    const el = document.documentElement;
    const toRemove: string[] = [];
    for (let i = 0; i < el.style.length; i++) {
        if (el.style[i].startsWith('--')) {
            toRemove.push(el.style[i]);
        }
    }
    toRemove.forEach((v) => el.style.removeProperty(v));
}
