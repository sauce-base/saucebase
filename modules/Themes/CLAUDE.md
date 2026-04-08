# Themes Module

A developer-facing visual theming system for Saucebase. Lets the SaaS owner design and bake a global app theme without writing CSS manually.

**Core flow:** Design visually in ThemePanel → export JSON → commit JSON → run `php artisan saucebase:theme:apply {theme}` → rebuild assets.

---

## Features

- 15 built-in themes (food-named: beetroot, coffee, kiwi, etc.) + customisable default
- Live visual editor (ThemePanel) — color pickers, font selectors, shadow & radius sliders
- Dark/light mode support — each theme defines both modes
- Cross-mode sync — per-field toggle icon button (lock/link) in each row; active = linked across modes
- Shadow system — 6 component vars compute 8 shadow scale strings via `computeShadows()`
- Radius system — single `--radius` base; `computeRadiusScale()` computes 7-step scale via `calc()`
- Tracking system — single `--tracking-normal` base; `computeTrackingScale()` computes 6-step scale
- Google Fonts — loaded on-demand via `<link>` injection; font classes injected per-var
- Theme picker with animated ripple transition between themes
- Theme persistence via `localStorage` key `sb-theme-theme`
- Save button is a **dropdown**: "Save" (update existing custom theme) / "Save as new theme" (create new)

---

## Intended Usage

| Who | How |
|-----|-----|
| SaaS developer/owner | Design in ThemePanel → export JSON → `saucebase:theme:apply` → rebuild |
| End users | **Not intended** — ThemePanel is a developer tool |

Theme selection is **global** (one theme for all users). Per-user or per-tenant theming is out of scope.

---

## Architecture

### Data flow

```
resources/themes/{id}.json  ←  committed source of truth
        ↓  (ApplyThemeCommand)
resources/css/theme.css     ←  baked-in CSS vars (:root / .dark)
        ↓  (Vite build)
Browser: CSS defaults

ThemesServiceProvider       ←  parses JSON, merges theme+light / theme+dark
        ↓  (Inertia prop: themes.items)
ThemePanel (Vue)            ←  applies inline style overrides via applyThemeVars()
        ↓  (localStorage)
Per-page CSS var override   ←  overrides theme.css defaults in-browser
```

### JSON theme structure

Three sections per theme:

```json
{
  "cssVars": {
    "theme": {
      /* Mode-agnostic / structural:
         font-sans, font-serif, font-mono, spacing, tracking-normal
         radius + computed scale: radius-sm … radius-4xl
         tracking scale: tracking-tighter … tracking-widest
         shadow geometry: shadow-blur, shadow-spread, shadow-offset-x, shadow-offset-y
         shadow strings (light): shadow-2xs … shadow-2xl  ← computed from light values */
    },
    "light": {
      /* ONLY: all color vars + shadow-color + shadow-opacity */
    },
    "dark": {
      /* ONLY: all color vars + shadow-color + shadow-opacity */
    }
  }
}
```

**Important — PHP merge:** `ThemesServiceProvider::parseThemeFile()` merges
`theme + light` → `Theme.light` and `theme + dark` → `Theme.dark` before sending to the
frontend. The frontend `Theme` object always has all vars in both `.light` and `.dark`.

### What belongs where

| Var | Section | Reason |
|-----|---------|--------|
| `font-*`, `spacing` | `theme` | Never mode-specific |
| `radius` + `radius-sm … radius-4xl` | `theme` | Structural; scale computed by JS and stored |
| `tracking-normal` + `tracking-tighter … tracking-widest` | `theme` | Structural; scale computed by JS |
| `shadow-blur/spread/offset-x/offset-y` | `theme` | Structural — same in light/dark |
| `shadow-2xs … shadow-2xl` | `theme` | Computed from light shadow vars; stored for CLI bake |
| `shadow-opacity` | `light` + `dark` | Legitimately varies (dark needs higher opacity) |
| `shadow-color` | `light` + `dark` | Always different per mode |
| All color vars | `light` + `dark` | Mode-specific appearance |
| `letter-spacing` | ❌ nowhere | Dead var — use `tracking-normal` |

### CSS variable layers

```
theme.css :root          ← baked defaults (light mode + mode-agnostic)
theme.css .dark          ← baked dark overrides (ONLY colors + shadow-color + shadow-opacity)
theme.css @theme inline  ← Tailwind mappings + radius scale
    ↓ overridden by
documentElement inline styles  ← set by applyThemeVars() when a theme is active
```

---

## Key Files

| File | Role |
|------|------|
| `resources/themes/*.json` | Theme definitions — committed source of truth |
| `resources/css/theme.css` | (in `resources/css/`, not inside module) — baked CSS output |
| `app/Console/Commands/ApplyThemeCommand.php` | Patches theme.css from JSON; writes `:root` and `.dark` blocks |
| `app/Providers/ThemesServiceProvider.php` | Discovers themes, parses JSON, shares via Inertia |
| `app/Http/Controllers/ThemesController.php` | REST API for save/update/delete of user themes |
| `resources/js/fields.ts` | Canonical list of all editable fields with type, vars, constraints |
| `resources/js/utils/theme.ts` | Core utilities: `applyThemeVars`, `computeShadows`, `computeRadiusScale`, `computeTrackingScale`, font loading |
| `resources/js/components/ThemePanel.vue` | Full visual editor — field rendering, per-field mode sync, save dropdown |
| `resources/js/components/ThemePicker.vue` | Theme switcher with ripple animation |

---

## Shadow System

Shadows are defined by **6 component vars** and computed into **8 shadow scale strings**.

**Component vars (stored in JSON):**
- `--shadow-color` — base color (per-mode, in light/dark)
- `--shadow-opacity` — opacity multiplier (per-mode, in light/dark; `perMode: true` in FIELD_DEFS)
- `--shadow-blur` — blur radius in px (mode-agnostic, in theme section)
- `--shadow-spread` — spread in px (mode-agnostic)
- `--shadow-offset-x` — x offset in px (mode-agnostic)
- `--shadow-offset-y` — y offset in px (mode-agnostic)

**Computed strings:** `--shadow-2xs` through `--shadow-2xl` — stored in JSON `theme` section (light values) and recomputed by JS on every theme load / mode switch.

`computeShadows()` in `utils/theme.ts` generates all 8 strings using `color-mix(in srgb, <color> X%, transparent)`. Called in:
1. `applyThemeVars()` — on every theme load / mode switch
2. `ThemePanel.vue` watch — on every shadow field edit (live preview)

---

## Radius Scale

Single source: `--radius` (stored in `theme` section of JSON).

`computeRadiusScale()` in `utils/theme.ts` returns 7 `calc()` strings:

```
--radius-sm:  calc(--radius - 4px)
--radius-md:  calc(--radius - 2px)
--radius-lg:  --radius  (no offset)
--radius-xl:  calc(--radius + 4px)
--radius-2xl: calc(--radius + 8px)
--radius-3xl: calc(--radius + 16px)
--radius-4xl: calc(--radius + 24px)
```

Computed scale is stored in the JSON `theme` section (for CLI bake) and applied as inline styles by `applyThemeVars()`.

---

## Tracking Scale

Single source: `--tracking-normal` (stored in `theme` section).

`computeTrackingScale()` in `utils/theme.ts` returns 6 em-offset strings:

```
--tracking-tighter: base - 0.050em
--tracking-tight:   base - 0.025em
--tracking-normal:  base + 0.000em
--tracking-wide:    base + 0.025em
--tracking-wider:   base + 0.050em
--tracking-widest:  base + 0.100em
```

---

## Field System (`fields.ts`)

All editable theme properties are defined in `FIELD_DEFS`. Each field has:
- `key` — matches JSON var name (without `--`)
- `type` — `color` | `unit` | `font`
- `vars` — CSS var names to write (always `--` prefixed)
- `group` — UI group (Brand, Surfaces, Typography, Shape, Shadow, Sidebar, Chart)
- `perMode` — `true` on `shadow-opacity` → `toJson()` writes it to `light`/`dark`, not `theme`

**Type behaviour in `toJson()`:**
- `color` → written to `light` + `dark`
- `unit` (default) → written to `theme` section, mode-agnostic
- `unit` with `perMode: true` → written to `light` + `dark` (like color)
- `font` → written to `theme` section; triggers Google Fonts load + class injection

`MANAGED_VARS_SET` (derived from `FIELD_DEFS`) is the allowlist — `applyThemeVars()` ignores any vars not in this set, including pre-computed shadow strings.

---

## Per-Field Mode Sync

`fieldSynced: Record<string, boolean>` (keyed by `field.key`) tracks which fields are linked across light/dark modes. Clicking the lock/link icon button on a field row toggles its sync state.

When a synced field is edited in one mode, the value is mirrored to `modeSyncCache` and applied when switching modes. On mode switch, the watcher restores all synced field values from the cache.

---

## Config

```env
THEMES_ENABLED=true          # Show/hide the entire ThemePanel UI
```

```php
// config/config.php
'enabled'       => env('THEMES_ENABLED', true),
```

---

## Commands

```bash
# Bake a theme into theme.css (primary workflow)
php artisan saucebase:theme:apply {theme-id}
# Then rebuild:
npm run build
# or restart dev server:
npm run dev

# List available themes
ls modules/Themes/resources/themes/
```

---

## Testing

```bash
# PHP tests
php -d memory_limit=2048M artisan test --compact modules/Themes/tests/

# E2E
npx playwright test --project="@Themes*"
```

Key test files:
- `tests/Feature/ApplyThemeCommandTest.php` — CLI patching logic
- `tests/Feature/ThemesControllerTest.php` — save/update/delete API
- `tests/Unit/ParseThemeFileTest.php` — JSON parsing and merging
- `tests/e2e/themes-config.spec.ts` — ThemePanel UI flows
