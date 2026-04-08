import { trans } from 'laravel-vue-i18n';
import type { FieldGroup, ThemeField } from './types';

const BRAND_GROUP: FieldGroup = { name: trans('Brand'), syncable: true };
const SURFACES_GROUP: FieldGroup = { name: trans('Surfaces'), collapsed: true, syncable: true };
const SEMANTIC_GROUP: FieldGroup = { name: trans('Semantic'), collapsed: true, syncable: true };
const TYPOGRAPHY_GROUP: FieldGroup = { name: trans('Typography'), collapsed: true };
const FOCUS_GROUP: FieldGroup = { name: trans('Borders & Focus'), collapsed: true };
const SHAPE_GROUP: FieldGroup = { name: trans('Shape'), collapsed: true};
const SHADOW_GROUP: FieldGroup = { name: trans('Shadow'), collapsed: true };
const SIDEBAR_GROUP: FieldGroup = { name: trans('Sidebar'), collapsed: true };
const CHART_GROUP: FieldGroup = { name: trans('Chart'), collapsed: true, syncable: true };

export const FIELD_DEFS: ThemeField[] = [
    // ── Brand ──────────────────────────────────────────────────────────────────
    {
        key: 'primary',
        label: trans('Primary'),
        type: 'color',
        vars: ['--primary'],
        group: BRAND_GROUP,
    },
    {
        key: 'primary-foreground',
        label: trans('Primary FG'),
        type: 'color',
        vars: ['--primary-foreground'],
        group: BRAND_GROUP,
    },
    {
        key: 'secondary',
        label: trans('Secondary'),
        type: 'color',
        vars: ['--secondary'],
        group: BRAND_GROUP,
    },
    {
        key: 'secondary-foreground',
        label: trans('Secondary FG'),
        type: 'color',
        vars: ['--secondary-foreground'],
        group: BRAND_GROUP,
    },

    // ── Surfaces ───────────────────────────────────────────────────────────────
    {
        key: 'background',
        label: trans('Background'),
        type: 'color',
        vars: ['--background'],
        group: SURFACES_GROUP,
    },
    {
        key: 'foreground',
        label: trans('Foreground'),
        type: 'color',
        vars: ['--foreground'],
        group: SURFACES_GROUP,
    },
    {
        key: 'card',
        label: trans('Card'),
        type: 'color',
        vars: ['--card'],
        group: SURFACES_GROUP,
    },
    {
        key: 'card-foreground',
        label: trans('Card FG'),
        type: 'color',
        vars: ['--card-foreground'],
        group: SURFACES_GROUP,
    },
    {
        key: 'popover',
        label: trans('Popover'),
        type: 'color',
        vars: ['--popover'],
        group: SURFACES_GROUP,
    },
    {
        key: 'popover-foreground',
        label: trans('Popover FG'),
        type: 'color',
        vars: ['--popover-foreground'],
        group: SURFACES_GROUP,
    },
    {
        key: 'muted',
        label: trans('Muted'),
        type: 'color',
        vars: ['--muted'],
        group: SURFACES_GROUP,
    },
    {
        key: 'muted-foreground',
        label: trans('Muted FG'),
        type: 'color',
        vars: ['--muted-foreground'],
        group: SURFACES_GROUP,
    },

    // ── Semantic ───────────────────────────────────────────────────────────────
    {
        key: 'accent',
        label: trans('Accent'),
        type: 'color',
        vars: ['--accent'],
        group: SEMANTIC_GROUP,
    },
    {
        key: 'accent-foreground',
        label: trans('Accent FG'),
        type: 'color',
        vars: ['--accent-foreground'],
        group: SEMANTIC_GROUP,
    },
    {
        key: 'destructive',
        label: trans('Destructive'),
        type: 'color',
        vars: ['--destructive'],
        group: SEMANTIC_GROUP,
    },

    // ── Typography ─────────────────────────────────────────────────────────────
    {
        key: 'font-body',
        label: trans('Default Font'),
        type: 'select',
        vars: ['--font-body'],
        group: TYPOGRAPHY_GROUP,
        props: {
            options: [
                { value: 'var(--font-sans)', label: trans('Sans') },
                { value: 'var(--font-serif)', label: trans('Serif') },
                { value: 'var(--font-mono)', label: trans('Mono') },
            ],
        },
    },
    {
        key: 'font-sans',
        label: trans('Sans'),
        type: 'font',
        vars: ['--font-sans'],
        group: TYPOGRAPHY_GROUP,
    },
    {
        key: 'font-serif',
        label: trans('Serif'),
        type: 'font',
        vars: ['--font-serif'],
        group: TYPOGRAPHY_GROUP,
    },
    {
        key: 'font-mono',
        label: trans('Mono'),
        type: 'font',
        vars: ['--font-mono'],
        group: TYPOGRAPHY_GROUP,
    },
    {
        key: 'tracking-normal',
        label: trans('Letter Spacing'),
        type: 'unit',
        vars: ['--tracking-normal'],
        props: { min: -0.1, max: 0.15, step: 0.005, unit: 'em' },
        group: TYPOGRAPHY_GROUP,
    },

    // ── Borders & Focus ────────────────────────────────────────────────────────
    {
        key: 'border',
        label: trans('Border'),
        type: 'color',
        vars: ['--border'],
        group: FOCUS_GROUP,
    },
    {
        key: 'input',
        label: trans('Input'),
        type: 'color',
        vars: ['--input'],
        group: FOCUS_GROUP,
    },
    {
        key: 'ring',
        label: trans('Ring'),
        type: 'color',
        vars: ['--ring'],
        group: FOCUS_GROUP,
    },

    // ── Shape ──────────────────────────────────────────────────────────────────
    {
        key: 'radius',
        label: trans('Radius'),
        type: 'unit',
        vars: ['--radius'],
        props: { min: 0, max: 1.5, step: 0.125, unit: 'rem' },
        group: SHAPE_GROUP,
    },
    {
        key: 'spacing',
        label: trans('Spacing'),
        type: 'unit',
        vars: ['--spacing'],
        props: { min: 0.15, max: 0.35, step: 0.005, unit: 'rem' },
        group: SHAPE_GROUP,
    },

    // ── Shadow ─────────────────────────────────────────────────────────────────
    {
        key: 'shadow-color',
        label: trans('Color'),
        type: 'color',
        vars: ['--shadow-color'],
        group: SHADOW_GROUP,
    },
    {
        key: 'shadow-opacity',
        label: trans('Opacity'),
        type: 'unit',
        vars: ['--shadow-opacity'],
        props: { min: 0, max: 1, step: 0.01 },
        perMode: true,
        group: SHADOW_GROUP,
    },
    {
        key: 'shadow-blur',
        label: trans('Blur'),
        type: 'unit',
        vars: ['--shadow-blur'],
        props: { min: 0, max: 100, step: 1, unit: 'px' },
        group: SHADOW_GROUP,
    },
    {
        key: 'shadow-spread',
        label: trans('Spread'),
        type: 'unit',
        vars: ['--shadow-spread'],
        props: { min: -50, max: 50, step: 1, unit: 'px' },
        group: SHADOW_GROUP,
    },
    {
        key: 'shadow-offset-x',
        label: trans('Offset X'),
        type: 'unit',
        vars: ['--shadow-offset-x'],
        props: { min: -50, max: 50, step: 1, unit: 'px' },
        group: SHADOW_GROUP,
    },
    {
        key: 'shadow-offset-y',
        label: trans('Offset Y'),
        type: 'unit',
        vars: ['--shadow-offset-y'],
        props: { min: -50, max: 50, step: 1, unit: 'px' },
        group: SHADOW_GROUP,
    },

    // ── Sidebar ────────────────────────────────────────────────────────────────
    {
        key: 'sidebar',
        label: trans('Background'),
        type: 'color',
        vars: ['--sidebar'],
        group: SIDEBAR_GROUP,
    },
    {
        key: 'sidebar-foreground',
        label: trans('Foreground'),
        type: 'color',
        vars: ['--sidebar-foreground'],
        group: SIDEBAR_GROUP,
    },
    {
        key: 'sidebar-primary',
        label: trans('Primary'),
        type: 'color',
        vars: ['--sidebar-primary'],
        group: SIDEBAR_GROUP,
    },
    {
        key: 'sidebar-primary-foreground',
        label: trans('Primary FG'),
        type: 'color',
        vars: ['--sidebar-primary-foreground'],
        group: SIDEBAR_GROUP,
    },
    {
        key: 'sidebar-accent',
        label: trans('Accent'),
        type: 'color',
        vars: ['--sidebar-accent'],
        group: SIDEBAR_GROUP,
    },
    {
        key: 'sidebar-accent-foreground',
        label: trans('Accent FG'),
        type: 'color',
        vars: ['--sidebar-accent-foreground'],
        group: SIDEBAR_GROUP,
    },
    {
        key: 'sidebar-border',
        label: trans('Border'),
        type: 'color',
        vars: ['--sidebar-border'],
        group: SIDEBAR_GROUP,
    },
    {
        key: 'sidebar-ring',
        label: trans('Ring'),
        type: 'color',
        vars: ['--sidebar-ring'],
        group: SIDEBAR_GROUP,
    },

    // ── Chart ──────────────────────────────────────────────────────────────────
    {
        key: 'chart-1',
        label: trans('Chart 1'),
        type: 'color',
        vars: ['--chart-1'],
        group: CHART_GROUP,
    },
    {
        key: 'chart-2',
        label: trans('Chart 2'),
        type: 'color',
        vars: ['--chart-2'],
        group: CHART_GROUP,
    },
    {
        key: 'chart-3',
        label: trans('Chart 3'),
        type: 'color',
        vars: ['--chart-3'],
        group: CHART_GROUP,
    },
    {
        key: 'chart-4',
        label: trans('Chart 4'),
        type: 'color',
        vars: ['--chart-4'],
        group: CHART_GROUP,
    },
    {
        key: 'chart-5',
        label: trans('Chart 5'),
        type: 'color',
        vars: ['--chart-5'],
        group: CHART_GROUP,
    },
];
