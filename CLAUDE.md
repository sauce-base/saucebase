# CLAUDE.md

## Project Overview

Saucebase is a modular Laravel SaaS starter kit. Modules are installed via Composer and owned directly in the repository (copy-and-own).

**Core message:** The foundation is built. Focus on your product. See the [Design Philosophy](https://saucebase-dev.github.io/docs/architecture/philosophy) doc for tone and value-proposition guidance.

**Stack:** Laravel 13, PHP 8.4+, Vue 3 Composition API, TypeScript 5.8, Inertia.js 3, Tailwind CSS 4, Vite 6.4, Filament 5 admin panel, Docker (Nginx, MySQL 8, Redis, Mailpit)

**Quality tools:** PHPStan level 5 (Larastan), Laravel Pint, ESLint, Prettier, PHPUnit 12, Playwright

## Commands

```bash
# Development (starts server, queue, logs, vite in parallel)
composer dev

# Tests
php artisan test                       # PHPUnit (all suites)
php artisan test --testsuite=Modules   # Module tests only
npm run test:e2e                       # Playwright E2E

# Code quality
composer analyse         # PHPStan level 5
composer lint            # Laravel Pint (PHP formatting)
npm run lint             # ESLint with auto-fix
npm run format           # Prettier

# Build
npm run build            # Production build (includes SSR)
npm run dev              # Vite dev server with HMR

# Modules
php artisan saucebase:recipe ModuleName     # Scaffold a new module from stubs
php artisan modules:list                    # List all discovered modules
php artisan modules:cache                   # Cache module discovery (production)
php artisan modules:clear                   # Clear module cache
# Installing a module (end-user workflow):
composer require saucebase/auth             # Installs to modules/auth/ via module-installer
composer remove saucebase/auth              # Removes module
# After any module change: rebuild with `npm run build` or restart `npm run dev`

# TypeScript types (per-module, generated from PHP enums/DTOs)
php artisan module:generate-types ModuleName   # single module
php artisan module:generate-types              # all enabled modules

# Framework selection (contributor dev workflow, via the global `saucebase` CLI) — see Architecture > Frontend for gotchas
saucebase stack {vue|react} --dev    # Switch active framework in dev mode (keeps both dirs)
saucebase stack {vue|react} --reset  # Reset to clean state (removes frontend.json)
```

## Architecture

### Module System

Uses `internachi/modular`. Modules are Composer packages installed into `modules/<modulename>/` by the `saucebase/module-installer` plugin. No enable/disable toggle — a module is active when `composer require`-d.

**Naming:** Folder names are always lowercase (`modules/auth/`, `modules/billing/`). PHP namespaces remain TitleCase (`Modules\Auth\...`) per PSR-4. The `modules().has()` JS helper uses lowercase: `modules().has('auth')`.

**Every module must provide both `vue/` and `react/` implementations.** The root `resources/js/app.ts` is a generated re-export — never the real implementation. Real implementation lives in `vue/app.ts` or `react/app.tsx`.

**Two distinct module contexts — understand which one applies:**

- **End users:** `composer require saucebase/auth` places files in `modules/auth/`. Files are committed to the user's repo (copy-and-own). `composer update saucebase/auth` overwrites local edits — commit customisations before updating. Do NOT gitignore `modules/`.
- **Core team:** The core repo ships with no modules committed. Module source repos in `modules/` (sibling dir) are linked via Composer path repositories (`"url": "modules/*", "symlink": true`). Symlinked dirs are not git-tracked. CI runs against the clean state.

**Module service provider:** Extend `App\Providers\ModuleServiceProvider`. No `$name` or `$nameLower` needed — InterNACHI derives the module name automatically. See `saucebase-module-development` skill for the full pattern.

**Asset discovery:** `module-loader.js` auto-collects assets, translations, and Playwright configs from installed modules. Don't bypass it.

**TypeScript types:** Each module generates its own `resources/js/types/generated.d.ts` from PHP classes annotated with `#[TypeScript]`. The core `config/typescript-transformer.php` only scans `app/`; module types use `module:generate-types`. Never edit `generated.d.ts` manually.

### Frontend

**Key files:**

- `resources/js/app.ts` — Main Inertia entry point
- `resources/js/ssr.ts` — SSR entry point
- `resources/js/lib/utils.ts` — `resolveModularPageComponent()` for module page resolution
- `resources/js/lib/moduleSetup.ts` — Module lifecycle management

**Multi-framework architecture:**

`frontend.json` at project root controls the active framework:
```json
{ "framework": "vue|react", "dev": true }
```
- `"dev": true` — contributor mode: both `vue/` and `react/` dirs kept, thin entry point passthroughs generated
- `"dev"` absent — end-user install: selected framework flattened to `resources/js/`, other dir deleted (one-time)

**`saucebase stack` modes:**
- `--dev` — contributor workflow: copies config files, creates entry passthroughs, keeps both framework dirs
- _(no flag)_ — end-user install: flattens one framework, deletes the other. Throws if `frontend.json` already exists
- `--reset` — restores git-tracked files, removes `frontend.json`

**End-user install (no `"dev"` key in `frontend.json`):**
- Framework files are flattened directly into `resources/js/` — no `vue/` or `react/` subdirectories exist.
- Write new pages to `resources/js/pages/`, components to `resources/js/components/`, etc.
- Same applies to modules: `modules/auth/resources/js/pages/` (flat), not `modules/auth/resources/js/vue/pages/`.
- `resources/js/app.ts` is the real entry point — edit it directly if needed.

**Dev mode gotchas — contributor/core-team context only (`"dev": true` in `frontend.json`):**
1. **Generated files** appear in `git status` but are passthroughs — never edit them directly:
   - `resources/js/app.ts` / `app.tsx`, `resources/js/ssr.ts` / `ssr.tsx`, `modules/*/resources/js/app.ts`
2. **Real edits go in `resources/js/vue/` or `resources/js/react/`** — never the root entry points.
3. **Config files** (`package.json`, `vite.config.js`, `tsconfig.json`, `eslint.config.js`, `components.json`, `resources/views/app.blade.php`) appear modified — commit only when intentionally changed.
4. **Can't run `--dev` twice** — use `--reset` first if switching frameworks.
5. **Cross-framework consistency** — any change to shared infrastructure must be applied to both `vue/` and `react/`.

**Vite aliases:** `@` = `resources/js/{framework}`, `@modules` = `modules/`, `@css` = `resources/css`, `ziggy-js` = vendor path

**TypeScript path aliases** (`tsconfig.json`): `@` = `resources/js`, `@modules` = `modules/`, `@e2e` = `tests/e2e`. Always use these — never relative `../../..` paths. Module E2E tests import core helpers as `@e2e/helpers/ssr`.

**Component library:** shadcn-vue style components in `resources/js/components/ui/` (copy-and-own, customizable)

**Dark/light mode — REQUIRED for all components.** Always include `dark:` variants. See `tailwindcss-development` skill for standard patterns.

**Translations:** `laravel-vue-i18n` with async loading. Core in `lang/`, modules in `modules/<modulename>/lang/`. Portuguese and English.

### Backend

**Key providers** (in `app/Providers/`): `MacroServiceProvider` (all macros incl. `withSSR`/`withoutSSR`), `ModuleServiceProvider` (abstract base for modules), `NavigationServiceProvider`, `BreadcrumbServiceProvider`, `Filament/AdminPanelProvider`.

**Permissions:** Spatie Laravel Permission. Default roles: admin, user (seeded via `RolesDatabaseSeeder`). Middleware: `role:admin|user`

**Admin panel:** Filament 5 at `/admin`. Default credentials (with Auth module): `chef@saucebase.dev` / `secretsauce`

**Helpers:** Auto-loaded from `app/Helpers/helpers.php`

### Testing

**PHPUnit suites:** Unit (`tests/Unit/`), Feature (`tests/Feature/`), Modules (`modules/*/tests/`). SQLite in-memory. Always run with `php -d memory_limit=2048M` to avoid OOM in the Modules suite.

**Playwright:** Auto-discovers module E2E tests. Projects prefixed `@ModuleName`, core as `@Core`. Default browser: Desktop Chrome.

**Playwright MCP screenshots:** Always save to `.playwright-mcp/` (already in `.gitignore`). Never save to `public/`, `resources/`, or any tracked directory.

**E2E selectors:** Always use `data-testid` — never select by translated text, labels, or role names. Item-specific testids: `{action}-${item.id}` (e.g. `upvote-btn-${item.id}`).

## Patterns & Conventions

### Inertia Page Resolution

```php
return inertia('Dashboard');          // resources/js/pages/Dashboard.vue
return inertia('Auth::Login');        // modules/auth/resources/js/pages/Login.vue
return inertia('Settings::Index');    // modules/settings/resources/js/pages/Index.vue
```

### SSR Control

Two-level system: middleware disables SSR by default per request, controllers opt in/out.

```php
return Inertia::render('Index')->withSSR();       // Enable (public/SEO pages)
return Inertia::render('Dashboard')->withoutSSR(); // Disable (authenticated pages)
return Inertia::render('About');                   // Default: SSR disabled
```

`HandleInertiaRequests` middleware sets `Config::set('inertia.ssr.enabled', false)` per request. Macros in `MacroServiceProvider` override this per response.

### Ziggy Routes

`route()` available in JS/TS via ZiggyVue plugin. Routes shared via Inertia middleware for SSR compatibility. Config: `config/ziggy.php`.

```typescript
route('dashboard');
route('user.show', { id: 1 });
route().current('settings.*');
```

### Macros

All macros in `MacroServiceProvider`. Add new macros there, organized by protected methods (e.g., `registerInertiaMacros()`).

### Navigation

Spatie Laravel Navigation, configured in `NavigationServiceProvider`. Module frontend navigation registered in `routes/navigation.php` per module (leave empty for admin-only modules).

### Environment Variables

Saucebase-specific: `APP_HOST`, `APP_URL`, `APP_SLUG`

SSL: Auto-enforced HTTPS in production/staging. Wildcard cert (`*.localhost`) for multi-tenancy support.

## Workflow

### Keeping CLAUDE.md Current

When any architectural decision, convention, or reference documented here changes — new module patterns, stack upgrades, renamed providers, new environment variables, altered file paths — **update this file in the same commit**. Stale CLAUDE.md content causes Claude to give confidently wrong advice.

Trigger: any change to stack versions, module structure, service providers, naming conventions, environment variables, or workflow commands.

### Testing CI Workflow Changes with act

When any file under `.github/workflows/` or `.github/actions/` is modified, validate with `act` before finishing. Always pass `-P ubuntu-latest=catthehacker/ubuntu:act-22.04` — do not use `--container-architecture linux/amd64` (breaks Node on Apple Silicon).

**Core workflow** (`test.yml` or `setup-laravel/action.yml` changed):

```bash
act workflow_dispatch --job phpunit --matrix framework:vue \
  --workflows .github/workflows/test.yml \
  -P ubuntu-latest=catthehacker/ubuntu:act-22.04

act workflow_dispatch --job phpunit --matrix framework:react \
  --workflows .github/workflows/test.yml \
  -P ubuntu-latest=catthehacker/ubuntu:act-22.04
```

**Module workflow** (`test-module.yml` changed):

```bash
act workflow_call \
  --workflows .github/workflows/test-module.yml \
  -P ubuntu-latest=catthehacker/ubuntu:act-22.04 \
  --input module=auth \
  --input frameworks='["vue"]' \
  --job phpunit
```

**Known act limitation:** SQLite env vars appended to `.env` do not persist between Docker exec steps in act, causing migrations to fail. This is a pre-existing act issue — not a regression. Real GitHub CI runs migrations correctly.

If both core and module workflows changed, run both sets of commands.

### Code Review (on demand)

Run `/code-review` to launch the code review agent (`feature-dev:code-reviewer`). Do not run it automatically.

### Moving Files Involving Modules

Before using `git mv` or any git-level file operation that touches `modules/`, check whether the module directory is its own git repository (`git -C modules/<name> rev-parse --git-dir 2>/dev/null`). If it is, **never use `git mv` from the root repo** — the module's files must be tracked by its own repo, not the core. Use plain `mv` instead: the core repo will see a deletion, the module repo will see a new file.

## Working Style

**Ask before assuming.** When context is missing — a file path, a git repo URL, an install workflow, a third-party service — stop and ask rather than searching the entire codebase or inferring from adjacent projects. State clearly what you need and why, then wait for the answer.

## Implementation Philosophy

- **Minimum viable implementation** — simplest solution that solves the problem
- Prefer fewer files, fewer abstractions, less code
- If it can be done in 5 lines, don't write 50
- A macro beats a middleware + gateway + config system
- No new interfaces/abstractions for single implementations
- No patterns (Factory, Strategy) for simple tasks
- Plans should be explainable in one sentence

## Commit Standards

Format: `type(scope): subject` or `type: subject`

All lowercase, single-line only, max 150 chars. Enforced by Husky (pre-commit only).

**Pre-commit hooks:** `composer lint` (PHP), `lint-staged` (ESLint + Prettier on JS/TS/Vue).

| Type       | Description                  |
| ---------- | ---------------------------- |
| `feat`     | New feature                  |
| `fix`      | Bug fix                      |
| `docs`     | Documentation                |
| `style`    | Formatting (no logic change) |
| `refactor` | Neither fix nor feature      |
| `perf`     | Performance                  |
| `test`     | Tests                        |
| `chore`    | Build/tooling                |
| `ci`       | CI config                    |
| `build`    | Build system/deps            |
| `revert`   | Revert previous commit       |
