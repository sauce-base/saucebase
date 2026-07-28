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

### Verification

Run the smallest relevant checks from `CONTRIBUTING.md`. Run module PHPUnit
tests with a 2048 MB PHP memory limit.

Update these source guidelines when a durable project convention changes, then
regenerate agent instructions with `composer boost:update`. Never edit the
generated Laravel Boost blocks in `AGENTS.md` or `CLAUDE.md` directly.
