## Saucebase

Saucebase is a modular Laravel SaaS starter kit. Treat the implementation and
manifests as authoritative; do not infer dependency versions or tool settings
from prose.

### Sources of Truth

- Backend dependencies and constraints: `composer.json`
- Static-analysis configuration: `phpstan.neon`
- Vue stack: `stubs/saucebase/stack/vue/package.json`
- React stack: `stubs/saucebase/stack/react/package.json`
- Module behavior: `app/Providers/ModuleServiceProvider.php`,
  `module-loader.js`, and the recipe stubs

The root `package.json` is framework-neutral before stack selection. Do not use
it alone to determine the supported Vue or React dependencies.

### Module Conventions

Modules are copy-and-own Composer packages installed under lowercase
`modules/<name>/` directories. PHP namespaces remain TitleCase.

An installed Composer module is active; there is no enable/disable toggle.
Never bypass `module-loader.js` for module assets, translations, or Playwright
project discovery.

Every main module provider extends `App\Providers\ModuleServiceProvider`. Do not
add `$name` or `$nameLower`: the base provider resolves the module name through
`ModuleRegistry::moduleForClass()`.

Use lowercase module identifiers in frontend checks such as
`modules().has('auth')`.

### Frontend Conventions

Saucebase supports both Vue and React. Apply shared frontend infrastructure
changes to both implementations.

In contributor mode, edit the real sources under `resources/js/vue/` and
`resources/js/react/`. Do not edit generated root entry-point passthroughs or
generated TypeScript declarations.

All components must support light and dark themes. Use stable `data-testid`
attributes for E2E selectors; never select translated text, labels, or role
names. Item-specific selectors use `{action}-${item.id}`.

### Settings Modal

Account and workspace settings are one modal over the current page, addressed by
the URL fragment `#settings/<slug>`. There is no settings page, layout, or
sidebar route.

A module contributes a panel by putting an `App\Settings\SettingsSection`
subclass in its own `src/Settings` directory; `SectionRegistry` discovers it
there. Only the requested section's `props()` runs — the rest are
`Inertia::optional()` and resolve when the user switches to them.

Two rules the fragment imposes:

- **Links that open settings stay plain anchors.** Inertia's `Link` calls
  `preventDefault()` and writes history with `pushState`, so the browser never
  fires `hashchange` and nothing hears the fragment change. Use `settingsHref()`
  (`useSettingsModal`), never `Link`.
- **Overlays inside a panel must portal into the modal**, not the body. The
  modal traps focus with a document-level `focusin` listener, so a body-portalled
  dialog or menu sits outside that subtree and the two traps recurse until the
  stack overflows. Core's `dialog` and `dropdown-menu` already read
  `useOverlayContainer()`; a panel needs no change, but a new overlay primitive
  does.

Panels render a bare `space-y-8` block with an `h2` title and a muted `p`
description — no `Card` shell, which the modal already provides — and carry a
`settings-<slug>-panel` test id.

### Verification

Run the smallest relevant checks from `CONTRIBUTING.md`. Run module PHPUnit
tests with a 2048 MB PHP memory limit.

Update these source guidelines when a durable project convention changes, then
regenerate agent instructions with `composer boost:update`. Never edit the
generated Laravel Boost blocks in `AGENTS.md` or `CLAUDE.md` directly.
