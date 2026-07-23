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

===

<laravel-boost-guidelines>
=== .ai/saucebase-core rules ===

## Saucebase

Saucebase is a modular Laravel SaaS starter kit. All features are encapsulated as **modules** under `modules/<modulename>/` (always lowercase). Modules are copy-and-own: once installed they live in the repo and can be edited freely.

### Module Creation

Use `php artisan saucebase:recipe {modulename}` to scaffold a new module from stubs. After scaffolding: `composer dump-autoload` → rebuild assets. There is no enable/disable toggle — a module is active when `composer require`-d.

### Module System

Modules are managed by `internachi/modular`. Module folder names are always lowercase (`modules/auth/`, `modules/billing/`). PHP namespaces remain TitleCase (`Modules\Auth\...`) per PSR-4.

**Module discovery:** `module-loader.js` auto-collects assets, translations, and Playwright configs from installed modules. Never bypass it.

**Inertia page resolution** — namespace prefix is TitleCase (PHP namespace), folder path is lowercase:

<code-snippet name="Inertia rendering" lang="php">
return inertia('Dashboard');           // resources/js/pages/Dashboard.vue
return inertia('Auth::Login');         // modules/auth/resources/js/pages/Login.vue
return inertia('Roadmap::Index');      // modules/roadmap/resources/js/pages/Index.vue
</code-snippet>

**SSR control** — opt in per response, not globally:

<code-snippet name="SSR control macros" lang="php">
return Inertia::render('Index')->withSSR();        // public/SEO pages
return Inertia::render('Dashboard')->withoutSSR(); // authenticated pages
</code-snippet>

### Required: Dark Mode

Every Vue component **must** include both light and dark variants using `dark:` prefix. Standard patterns:

- Backgrounds: `bg-white dark:bg-gray-900`
- Text primary: `text-gray-900 dark:text-white`
- Text secondary: `text-gray-600 dark:text-gray-400`
- Borders: `border-gray-200 dark:border-gray-800`

### Required: E2E Selectors

Always use `data-testid` attributes — never select by translated text, labels, or role names. Item-specific IDs: `{action}-${item.id}` (e.g. `upvote-btn-${item.id}`).

### Module Service Provider Pattern

Every module's main service provider must extend `App\Providers\ModuleServiceProvider`. No `$name` or `$nameLower` needed — InterNACHI derives the module name automatically via `ModuleRegistry::moduleForClass()`.

<code-snippet name="Module service provider" lang="php">
class FeatureServiceProvider extends ModuleServiceProvider
{
    protected array $providers = [
        RouteServiceProvider::class,
    ];

    // Optional: override to share data on every Inertia response
    protected function shareInertiaData(): void
    {
        Inertia::share('key', fn () => ...);
    }

}
</code-snippet>

### Filament Plugin Pattern

Every module that adds Filament resources must have a plugin class implementing `Filament\Contracts\Plugin` and using the `App\Filament\ModulePlugin` trait. The plugin is auto-discovered by convention: `Modules\{Name}\Filament\{Name}Plugin`.

<code-snippet name="Filament module plugin" lang="php">
class FeaturePlugin implements Plugin
{
    use ModulePlugin;

    public function getModuleName(): string { return 'Feature'; }
    public function getId(): string { return 'feature'; }
    public function boot(Panel $panel): void { /* navigation groups, etc. */ }

}
</code-snippet>

### Navigation

Frontend navigation is registered in `routes/navigation.php` per module. If a module has no frontend pages (admin-only), leave this file empty.

<code-snippet name="Module navigation registration" lang="php">
Navigation::add('Feature', route('feature.index'), function (Section $section) {
    $section->attributes([
        'group' => 'main',
        'slug' => 'feature',
        'icon' => 'feature-icon-name',
    ]);
});
</code-snippet>

Icons are registered in the module's `resources/js/app.ts` via `registerIcon()`.

### TypeScript Types

Module types are generated separately from the core app:

```bash
php artisan module:generate-types featurename   # regenerate after PHP enum/DTO changes

```

**`modules().has()` in Vue/TS:** always use lowercase — `modules().has('auth')`, `modules().has('billing')`. The composable is case-insensitive but lowercase is the canonical form matching the registry.

Never edit `resources/js/types/generated.d.ts` manually — it is auto-generated.

### Testing Commands

```bash

# PHPUnit — single module

php -d memory_limit=2048M artisan test --testsuite=Modules --filter='^Modules\\FeatureName\\Tests'

# E2E — single module

npx playwright test --project="@FeatureName*"
```

Always run with `php -d memory_limit=2048M` to avoid OOM errors in the Modules suite.

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- filament/filament (FILAMENT) - v5
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/ai (AI) - v0
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- livewire/livewire (LIVEWIRE) - v4
- tightenco/ziggy (ZIGGY) - v2
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/telescope (TELESCOPE) - v5
- phpunit/phpunit (PHPUNIT) - v12

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Follow existing application Enum naming conventions.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

=== filament/filament rules ===

## Filament

- Filament is a Laravel UI framework built on Livewire, Alpine.js, and Tailwind CSS. UIs are defined in PHP via fluent, chainable components. Follow existing conventions in this app.
- Use the `search-docs` tool for official documentation on Artisan commands, code examples, testing, relationships, and idiomatic practices. If `search-docs` is unavailable, refer to https://filamentphp.com/docs.

### Artisan

- Always use Filament-specific Artisan commands to create files. Find available commands with the `list-artisan-commands` tool, or run `php artisan --help`.
- Inspect required options before running, and always pass `--no-interaction`.

### Patterns

Always use static `make()` methods to initialize components. Most configuration methods accept a `Closure` for dynamic values.

Use `Get $get` to read other form field values for conditional logic:

<code-snippet name="Conditional form field visibility" lang="php">
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

Select::make('type')
    ->options(CompanyType::class)
    ->required()
    ->live(),

TextInput::make('company_name')
    ->required()
    ->visible(fn (Get $get): bool => $get('type') === 'business'),

</code-snippet>

Use `Set $set` inside `->afterStateUpdated()` on a `->live()` field to mutate another field reactively. Prefer `->live(onBlur: true)` on text inputs to avoid per-keystroke updates:

<code-snippet name="Reactive field update" lang="php">
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

TextInput::make('title')
    ->required()
    ->live(onBlur: true)
    ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
        'slug',
        Str::slug($state ?? ''),
    )),

TextInput::make('slug')
    ->required(),

</code-snippet>

Compose layout by nesting `Section` and `Grid`. Children need explicit `->columnSpan()` or `->columnSpanFull()`:

<code-snippet name="Section and Grid layout" lang="php">
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

Section::make('Details')
    ->schema([
        Grid::make(2)->schema([
            TextInput::make('first_name')
                ->columnSpan(1),
            TextInput::make('last_name')
                ->columnSpan(1),
            TextInput::make('bio')
                ->columnSpanFull(),
        ]),
    ]),

</code-snippet>

Use `Repeater` for inline `HasMany` management. `->relationship()` with no args binds to the relationship matching the field name:

<code-snippet name="Repeater for HasMany" lang="php">
use Filament\Forms\Components\Repeater;

Repeater::make('qualifications')
    ->relationship()
    ->schema([
        TextInput::make('institution')
            ->required(),
        TextInput::make('qualification')
            ->required(),
    ])
    ->columns(2),

</code-snippet>

Use `state()` with a `Closure` to compute derived column values:

<code-snippet name="Computed table column value" lang="php">
use Filament\Tables\Columns\TextColumn;

TextColumn::make('full_name')
    ->state(fn (User $record): string => "{$record->first_name} {$record->last_name}"),

</code-snippet>

Use `SelectFilter` for enum or relationship filters, and `Filter` with a `->query()` closure for custom logic:

<code-snippet name="Table filters" lang="php">
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

SelectFilter::make('status')
    ->options(UserStatus::class),

SelectFilter::make('author')
    ->relationship('author', 'name'),

Filter::make('verified')
    ->query(fn (Builder $query) => $query->whereNotNull('email_verified_at')),

</code-snippet>

Actions are buttons that encapsulate optional modal forms and behavior:

<code-snippet name="Action with modal form" lang="php">
use Filament\Actions\Action;

Action::make('updateEmail')
    ->schema([
        TextInput::make('email')
            ->email()
            ->required(),
    ])
    ->action(fn (array $data, User $record) => $record->update($data)),

</code-snippet>

### Testing

Testing setup (requires `pestphp/pest-plugin-livewire` in `composer.json`):

- Always call `$this->actingAs(User::factory()->create())` before testing panel functionality.
- For edit pages, pass `['record' => $user->id]`, use `->call('save')` (not `->call('create')`), and do not assert `->assertRedirect()` (edit pages do not redirect after save).

<code-snippet name="Table test" lang="php">
use function Pest\Livewire\livewire;

livewire(ListUsers::class)
    ->assertCanSeeTableRecords($users)
    ->searchTable($users->first()->name)
    ->assertCanSeeTableRecords($users->take(1))
    ->assertCanNotSeeTableRecords($users->skip(1));

</code-snippet>

<code-snippet name="Create resource test" lang="php">
use function Pest\Laravel\assertDatabaseHas;

livewire(CreateUser::class)
    ->fillForm([
        'name' => 'Test',
        'email' => 'test@example.com',
    ])
    ->call('create')
    ->assertNotified()
    ->assertHasNoFormErrors()
    ->assertRedirect();

assertDatabaseHas(User::class, [
    'name' => 'Test',
    'email' => 'test@example.com',
]);

</code-snippet>

<code-snippet name="Edit resource test" lang="php">
livewire(EditUser::class, ['record' => $user->id])
    ->fillForm(['name' => 'Updated'])
    ->call('save')
    ->assertNotified()
    ->assertHasNoFormErrors();

assertDatabaseHas(User::class, [
    'id' => $user->id,
    'name' => 'Updated',
]);

</code-snippet>

<code-snippet name="Testing validation" lang="php">
livewire(CreateUser::class)
    ->fillForm([
        'name' => null,
        'email' => 'invalid-email',
    ])
    ->call('create')
    ->assertHasFormErrors([
        'name' => 'required',
        'email' => 'email',
    ])
    ->assertNotNotified();

</code-snippet>

Use `->callAction(DeleteAction::class)` for page actions, or `->callAction(TestAction::make('name')->table($record))` for table actions:

<code-snippet name="Calling actions" lang="php">
use Filament\Actions\Testing\TestAction;

livewire(ListUsers::class)
    ->callAction(TestAction::make('promote')->table($user), [
        'role' => 'admin',
    ])
    ->assertNotified();

</code-snippet>

### Correct Namespaces

- Form fields (`TextInput`, `Select`, `Repeater`, etc.): `Filament\Forms\Components\`
- Infolist entries (`TextEntry`, `IconEntry`, etc.): `Filament\Infolists\Components\`
- Layout components (`Grid`, `Section`, `Fieldset`, `Tabs`, `Wizard`, etc.): `Filament\Schemas\Components\`
- Schema utilities (`Get`, `Set`, etc.): `Filament\Schemas\Components\Utilities\`
- Table columns (`TextColumn`, `IconColumn`, etc.): `Filament\Tables\Columns\`
- Table filters (`SelectFilter`, `Filter`, etc.): `Filament\Tables\Filters\`
- Actions (`DeleteAction`, `CreateAction`, etc.): `Filament\Actions\`. Never use `Filament\Tables\Actions\`, `Filament\Forms\Actions\`, or any other sub-namespace for actions.
- Icons: `Filament\Support\Icons\Heroicon` enum (e.g., `Heroicon::PencilSquare`)

### Common Mistakes

- **Never assume public file visibility.** File visibility is `private` by default. Always use `->visibility('public')` when public access is needed.
- **Never assume full-width layout.** `Grid`, `Section`, `Fieldset`, and `Repeater` do not span all columns by default.
- **Use `Select::make('author_id')->relationship('author', 'name')` for BelongsTo fields.** `BelongsToSelect` does not exist in v4.
- **`Repeater` uses `->schema()`, not `->fields()`.**
- **Never add `->dehydrated(false)` to fields that need to be saved.** It strips the value from form state before `->action()` or the save handler runs. Only use it for helper/UI-only fields.
- **Use correct property types when overriding `Page`, `Resource`, and `Widget` properties.** These properties have union types or changed modifiers that must be preserved:
  - `$navigationIcon`: `protected static string | BackedEnum | null` (not `?string`)
  - `$navigationGroup`: `protected static string | UnitEnum | null` (not `?string`)
  - `$view`: `protected string` (not `protected static string`) on `Page` and `Widget` classes

</laravel-boost-guidelines>
