# CLAUDE.md

## Project Overview

Saucebase is a modular Laravel SaaS starter kit (VILT stack). Modules are installed via Composer and owned directly in the repository (copy-and-own).

**Core message:** The foundation is built. Focus on your product. See [`docs/CLAUDE.md`](../docs/CLAUDE.md) → _Saucebase Philosophy_ for tone and value-proposition guidance.

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
php artisan saucebase:recipe ModuleName     # Scaffold a new module from a recipe (stubs)
# After scaffolding: composer dump-autoload && php artisan module:enable ModuleName
php artisan module:list
php artisan module:enable ModuleName
php artisan module:disable ModuleName
php artisan module:migrate ModuleName --seed
# After enable/disable: rebuild with `npm run build` or restart `npm run dev`

# TypeScript types (per-module, generated from PHP enums/DTOs)
php artisan module:generate-types ModuleName   # single module
php artisan module:generate-types              # all enabled modules
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
  resources/js/types/
    generated.d.ts        # Auto-generated from PHP enums/DTOs — do not edit manually
  resources/css/
  routes/web.php
  routes/api.php
  tests/Feature/
  tests/Unit/
  tests/e2e/
  vite.config.js          # Module asset paths: { paths: ['css/app.css', 'js/app.ts'] }
  Taskfile.yml            # Module tasks (test:php, test:e2e, types:generate)
  module.json
```

**TypeScript type generation:** Each module generates its own `resources/js/types/generated.d.ts` from PHP classes annotated with `#[TypeScript]` (enums, Spatie Data objects). The core `config/typescript-transformer.php` only scans `app/`; module types are generated separately via `module:generate-types`. `tsconfig.json` includes `modules/**/resources/js/**/*.ts` so generated files are picked up automatically.

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

> **Clean state:** The main repository has no modules committed (`modules/` is empty except for `.gitkeep`).
> Modules (Auth, Billing, Settings, etc.) are installed locally for development via Composer and owned in-repo,
> but they are NOT tracked in version control. CI runs against the clean state.

**Asset discovery:** `module-loader.js` auto-collects assets, translations, and Playwright configs from enabled modules. Don't bypass it.

### Frontend

**Key files:**

- `resources/js/app.ts` — Main Inertia entry point
- `resources/js/ssr.ts` — SSR entry point
- `resources/js/lib/utils.ts` — `resolveModularPageComponent()` for module page resolution
- `resources/js/lib/moduleSetup.ts` — Module lifecycle management

**Vite aliases:** `@` = `resources/js`, `@modules` = `modules/`, `ziggy-js` = vendor path

**TypeScript path aliases** (`tsconfig.json`): `@` = `resources/js`, `@modules` = `modules/`, `@e2e` = `tests/e2e`. Always use these aliases — never use relative `../../..` paths. Module E2E tests import core helpers as `@e2e/helpers/ssr`, not `../../../../tests/e2e/helpers/ssr`.

**Component library:** shadcn-vue style components in `resources/js/components/ui/` (copy-and-own, customizable)

**Dark/light mode — REQUIRED for all components:**

Always include both light and dark variants. Use Tailwind `dark:` prefix.

Common patterns:

- Backgrounds: `bg-white dark:bg-gray-900` or `bg-gray-50 dark:bg-gray-900`
- Text primary: `text-gray-900 dark:text-white`
- Text secondary: `text-gray-600 dark:text-gray-400`
- Borders: `border-gray-200 dark:border-gray-800`
- Links: `font-medium text-primary/70 hover:underline underline-offset-4`

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

**Playwright MCP screenshots:** Always save to `.playwright-mcp/` (already in `.gitignore`). Never save screenshots to `public/`, `resources/`, or any other tracked directory.

**E2E selectors:** Always use `data-testid` attributes — never select by translated text, labels, or role names. Add `data-testid` to any interactive element that E2E tests need to target. Item-specific testids use the pattern `{action}-${item.id}` (e.g. `upvote-btn-${item.id}`).

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

Saucebase-specific: `APP_HOST`, `APP_URL`, `APP_SLUG`,

SSL: Auto-enforced HTTPS in production/staging. Wildcard cert (`*.localhost`) for multi-tenancy support.

## Workflow

### Keeping CLAUDE.md Current

When any architectural decision, convention, or reference documented here changes — new module patterns, stack upgrades, renamed providers, new environment variables, altered file paths — **update this file in the same commit**. Stale CLAUDE.md content causes Claude to give confidently wrong advice.

Trigger: any change to stack versions, module structure, service providers, naming conventions, environment variables, or workflow commands.

### Code Review (on demand)

Run `/code-review` to launch the code review agent (`feature-dev:code-reviewer`). Do not run it automatically.

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

Saucebase is a modular Laravel SaaS starter kit (VILT stack). All features are encapsulated as **modules** under `modules/<ModuleName>/`. Modules are copy-and-own: once installed they live in the repo and can be edited freely.

### Module Creation

Use `php artisan saucebase:recipe {ModuleName}` to scaffold a new module from stubs. After scaffolding: `composer dump-autoload` → `php artisan module:enable ModuleName` → rebuild assets.

### Module System

Modules are managed by `nwidart/laravel-modules`. Enable state is tracked in `modules_statuses.json`.

**Module discovery:** `module-loader.js` auto-collects assets, translations, and Playwright configs from enabled modules. Never bypass it.

**Inertia page resolution:**

<code-snippet name="Inertia rendering" lang="php">
return inertia('Dashboard');           // resources/js/pages/Dashboard.vue
return inertia('Auth::Login');         // modules/Auth/resources/js/pages/Login.vue
return inertia('Roadmap::Index');      // modules/Roadmap/resources/js/pages/Index.vue
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

Every module's main service provider must extend `App\Providers\ModuleServiceProvider` and define `$name` and `$nameLower`. Both properties are required — the base class throws a `LogicException` if either is missing.

<code-snippet name="Module service provider" lang="php">
class FeatureServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Feature';
    protected string $nameLower = 'feature';

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
php artisan module:generate-types FeatureName   # regenerate after PHP enum/DTO changes

```

Never edit `resources/js/types/generated.d.ts` manually — it is auto-generated.

### Testing Commands

```bash

# PHPUnit — single module

php -d memory_limit=2048M artisan test --testsuite=Modules --filter='^Modules\FeatureName\Tests'

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
- laravel/socialite (SOCIALITE) - v5
- livewire/livewire (LIVEWIRE) - v4
- tightenco/ziggy (ZIGGY) - v2
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/telescope (TELESCOPE) - v5
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/vue3 (INERTIA_VUE) - v3
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `ai-sdk-development` — TRIGGER when working with ai-sdk which is Laravel official first-party AI SDK. Activate when building, editing AI agents, chatbots, text generation, image generation, audio/TTS, transcription/STT, embeddings, RAG, vector stores, reranking, structured output, streaming, conversation memory, tools, queueing, broadcasting, and provider failover across OpenAI, Anthropic, Gemini, Azure, Groq, xAI, DeepSeek, Mistral, Ollama, ElevenLabs, Cohere, Jina, and VoyageAI. Invoke when the user references ai-sdk, the `Laravel\Ai\` namespace, or this project's AI features — not for Prism PHP or other AI packages used directly.
- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `socialite-development` — Manages OAuth social authentication with Laravel Socialite. Activate when adding social login providers; configuring OAuth redirect/callback flows; retrieving authenticated user details; customizing scopes or parameters; setting up community providers; testing with Socialite fakes; or when the user mentions social login, OAuth, Socialite, or third-party authentication.
- `inertia-vue-development` — Develops Inertia.js v3 Vue client-side applications. Activates when creating Vue pages, forms, or navigation; using <Link>, <Form>, useForm, useHttp, setLayoutProps, or router; working with deferred props, prefetching, optimistic updates, instant visits, or polling; or when user mentions Vue with Inertia, Vue pages, Vue forms, or Vue navigation.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.
- `saucebase-filament-development` — Guides Filament resource development inside Saucebase modules. Activate when creating Filament resources, tables, forms, infolists, or pages inside a module, adding actions/filters/bulk actions, registering navigation groups, or testing Filament resources.
- `saucebase-module-development` — Guides Saucebase module creation and development. Activate when scaffolding a new module, adding controllers/pages/migrations to a module, working with module service providers, Filament module plugins, or when user mentions saucebase:recipe, module:enable, or asks about module structure.

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
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
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
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

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

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

=== filament/filament rules ===

## Filament

- Filament is used by this application. Follow the existing conventions for how and where it is implemented.
- Filament is a Server-Driven UI (SDUI) framework for Laravel that lets you define user interfaces in PHP using structured configuration objects. Built on Livewire, Alpine.js, and Tailwind CSS.
- Use the `search-docs` tool for official documentation on Artisan commands, code examples, testing, relationships, and idiomatic practices. If `search-docs` is unavailable, refer to https://filamentphp.com/docs.

### Artisan

- Always use Filament-specific Artisan commands to create files. Find available commands with the `list-artisan-commands` tool, or run `php artisan --help`.
- Always inspect required options before running a command, and always pass `--no-interaction`.

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

Use `state()` with a `Closure` to compute derived column values:

<code-snippet name="Computed table column value" lang="php">
use Filament\Tables\Columns\TextColumn;

TextColumn::make('full_name')
    ->state(fn (User $record): string => "{$record->first_name} {$record->last_name}"),

</code-snippet>

Actions encapsulate a button with an optional modal form and logic:

<code-snippet name="Action with modal form" lang="php">
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

Action::make('updateEmail')
    ->schema([
        TextInput::make('email')
            ->email()
            ->required(),
    ])
    ->action(fn (array $data, User $record) => $record->update($data))

</code-snippet>

### Testing

Always authenticate before testing panel functionality. Filament uses Livewire, so use `Livewire::test()` or `livewire()` (available when `pestphp/pest-plugin-livewire` is in `composer.json`):

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
use function Pest\Livewire\livewire;

livewire(CreateUser::class)
    ->fillForm([
        'name' => 'Test',
        'email' => 'test@example.com',
    ])
    ->call('create')
    ->assertNotified()
    ->assertRedirect();

assertDatabaseHas(User::class, [
    'name' => 'Test',
    'email' => 'test@example.com',
]);

</code-snippet>

<code-snippet name="Testing validation" lang="php">
use function Pest\Livewire\livewire;

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

<code-snippet name="Calling actions in pages" lang="php">
use Filament\Actions\DeleteAction;
use function Pest\Livewire\livewire;

livewire(EditUser::class, ['record' => $user->id])
    ->callAction(DeleteAction::class)
    ->assertNotified()
    ->assertRedirect();

</code-snippet>

<code-snippet name="Calling actions in tables" lang="php">
use Filament\Actions\Testing\TestAction;
use function Pest\Livewire\livewire;

livewire(ListUsers::class)
    ->callAction(TestAction::make('promote')->table($user), [
        'role' => 'admin',
    ])
    ->assertNotified();

</code-snippet>

### Correct Namespaces

- Form fields (`TextInput`, `Select`, etc.): `Filament\Forms\Components\`
- Infolist entries (`TextEntry`, `IconEntry`, etc.): `Filament\Infolists\Components\`
- Layout components (`Grid`, `Section`, `Fieldset`, `Tabs`, `Wizard`, etc.): `Filament\Schemas\Components\`
- Schema utilities (`Get`, `Set`, etc.): `Filament\Schemas\Components\Utilities\`
- Actions (`DeleteAction`, `CreateAction`, etc.): `Filament\Actions\`. Never use `Filament\Tables\Actions\`, `Filament\Forms\Actions\`, or any other sub-namespace for actions.
- Icons: `Filament\Support\Icons\Heroicon` enum (e.g., `Heroicon::PencilSquare`)

### Common Mistakes

- **Never assume public file visibility.** File visibility is `private` by default. Always use `->visibility('public')` when public access is needed.
- **Never assume full-width layout.** `Grid`, `Section`, and `Fieldset` do not span all columns by default. Explicitly set column spans when needed.
- **Use correct property types when overriding Page, Resource, and Widget properties.** These properties have union types or changed modifiers that must be preserved:
  - `$navigationIcon`: `protected static string | BackedEnum | null` (not `?string`)
  - `$navigationGroup`: `protected static string | UnitEnum | null` (not `?string`)
  - `$view`: `protected string` (not `protected static string`) on Page and Widget classes

</laravel-boost-guidelines>
