# CLAUDE.md

## Project Overview

Saucebase is a modular Laravel SaaS starter kit (VILT stack). Modules are installed via Composer and owned directly in the repository (copy-and-own).

**Stack:** Laravel 12, PHP 8.4+, Vue 3 Composition API, TypeScript 5.8, Inertia.js 2, Tailwind CSS 4, Vite 6.4, Filament 5 admin panel, Docker (Nginx, MySQL 8, Redis, Mailpit)

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
php artisan module:list
php artisan module:enable ModuleName
php artisan module:disable ModuleName
php artisan module:migrate ModuleName --seed
# After enable/disable: rebuild with `npm run build` or restart `npm run dev`
```

## Architecture

### Module System

Uses `nwidart/laravel-modules`. Modules tracked in `modules_statuses.json` (`{"ModuleName": true}`).

**Currently installed modules:** Auth, Settings, Billing

```
modules/<ModuleName>/
  app/Http/Controllers/
  app/Models/
  app/Providers/          # Must extend App\Providers\ModuleServiceProvider
  config/
  database/migrations/
  database/seeders/
  lang/
  resources/js/pages/     # Inertia pages
  resources/js/components/
  resources/js/app.ts     # Module lifecycle hooks (optional)
  resources/css/
  routes/web.php
  routes/api.php
  tests/Feature/
  tests/Unit/
  tests/e2e/
  vite.config.js          # Module asset paths: { paths: ['css/app.css', 'js/app.ts'] }
  module.json
```

**Module service provider pattern:**

```php
class AuthServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Auth';
    protected string $nameLower = 'auth';
}
```

**Module lifecycle hooks** (`modules/<Name>/resources/js/app.ts`):

```typescript
export default {
    setup(app) {
        /* Before Vue mount — register plugins, components */
    },
    afterMount(app) {
        /* After mount — services needing DOM */
    },
};
```

**Asset discovery:** `module-loader.js` auto-collects assets, translations, and Playwright configs from enabled modules. Don't bypass it.

### Frontend

**Key files:**

- `resources/js/app.ts` — Main Inertia entry point
- `resources/js/ssr.ts` — SSR entry point
- `resources/js/lib/utils.ts` — `resolveModularPageComponent()` for module page resolution
- `resources/js/lib/moduleSetup.ts` — Module lifecycle management

**Vite aliases:** `@` = `resources/js`, `@modules` = `modules/`, `ziggy-js` = vendor path

**Component library:** shadcn-vue style components in `resources/js/components/ui/` (copy-and-own, customizable)

**Dark/light mode — REQUIRED for all components:**

Always include both light and dark variants. Use Tailwind `dark:` prefix.

Common patterns:

- Backgrounds: `bg-white dark:bg-gray-900` or `bg-gray-50 dark:bg-gray-900`
- Text primary: `text-gray-900 dark:text-white`
- Text secondary: `text-gray-600 dark:text-gray-400`
- Borders: `border-gray-200 dark:border-gray-800`
- Links: `text-indigo-600 dark:text-indigo-400`

**Translations:** `laravel-vue-i18n` with async loading. Core in `lang/`, modules in `modules/<Name>/lang/`. Portuguese and English.

### Backend

**Service providers:**

- `AppServiceProvider` — HTTPS enforcement, module event discovery fix
- `MacroServiceProvider` — All macros (`->withSSR()`, `->withoutSSR()`)
- `ModuleServiceProvider` (abstract) — Base for module providers (translations, config, migrations, Inertia data)
- `NavigationServiceProvider` — Spatie navigation
- `BreadcrumbServiceProvider` — Diglactic breadcrumbs
- `Filament/AdminPanelProvider` — Filament admin panel config
- `TelescopeServiceProvider` — Laravel Telescope

**Permissions:** Spatie Laravel Permission. Default roles: admin, user (seeded via `RolesDatabaseSeeder`). Middleware: `role:admin|user`

**Admin panel:** Filament 5 at `/admin`. Default credentials (with Auth module): `chef@saucebase.dev` / `secretsauce`

**Helpers:** Auto-loaded from `app/Helpers/helpers.php`

### Testing

**PHPUnit suites:** Unit (`tests/Unit/`), Feature (`tests/Feature/`), Modules (`modules/*/tests/`). SQLite in-memory by default.

**Playwright:** Auto-discovers module E2E tests. Projects prefixed `@ModuleName`, core as `@Core`. Default browser: Desktop Chrome.

## Patterns & Conventions

### Inertia Page Resolution

```php
return inertia('Dashboard');          // resources/js/pages/Dashboard.vue
return inertia('Auth::Login');        // modules/Auth/resources/js/pages/Login.vue
return inertia('Settings::Index');    // modules/Settings/resources/js/pages/Index.vue
```

### SSR Control

Two-level system: middleware disables SSR by default per request, controllers opt in/out.

```php
return Inertia::render('Index')->withSSR();       // Enable (public/SEO pages)
return Inertia::render('Dashboard')->withoutSSR(); // Disable (authenticated pages)
return Inertia::render('About');                   // Default: SSR disabled
```

`HandleInertiaRequests` middleware sets `Config::set('inertia.ssr.enabled', false)` per request. Macros defined in `MacroServiceProvider` override this per response.

### Ziggy Routes

`route()` function available in JS/TS via ZiggyVue plugin. Routes shared via Inertia middleware for SSR compatibility. Config: `config/ziggy.php`.

```typescript
route('dashboard');
route('user.show', { id: 1 });
route().current('settings.*');
```

### Macros

All macros in `MacroServiceProvider`. Add new macros there, organized by protected methods (e.g., `registerInertiaMacros()`).

### Navigation

Spatie Laravel Navigation, configured in `NavigationServiceProvider`.

### Environment Variables

Saucebase-specific: `APP_HOST`, `APP_URL`, `APP_SLUG`, `VITE_LOCAL_STORAGE_KEY`

SSL: Auto-enforced HTTPS in production/staging. Wildcard cert (`*.localhost`) for multi-tenancy support.

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

All lowercase, single-line only, max 150 chars. Enforced by commitlint + Husky.

**Pre-commit hooks:** `composer lint` (PHP), `lint-staged` (ESLint + Prettier on JS/TS/Vue), `commitlint` (message validation).

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
