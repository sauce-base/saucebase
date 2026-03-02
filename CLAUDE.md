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

## Workflow

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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.12
- filament/filament (FILAMENT) - v5
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v2
- laravel/ai (AI) - v0
- laravel/framework (LARAVEL) - v12
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
- @inertiajs/vue3 (INERTIA_VUE) - v2
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `inertia-vue-development` — Develops Inertia.js v2 Vue client-side applications. Activates when creating Vue pages, forms, or navigation; using &lt;Link&gt;, &lt;Form&gt;, useForm, or router; working with deferred props, prefetching, or polling; or when user mentions Vue with Inertia, Vue pages, Vue forms, or Vue navigation.
- `tailwindcss-development` — Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.
- `developing-with-ai-sdk` — Builds AI agents, generates text and chat responses, produces images, synthesizes audio, transcribes speech, generates vector embeddings, reranks documents, and manages files and vector stores using the Laravel AI SDK (laravel/ai). Supports structured output, streaming, tools, conversation memory, middleware, queueing, broadcasting, and provider failover. Use when building, editing, updating, debugging, or testing any AI functionality, including agents, LLMs, chatbots, text generation, image generation, audio, transcription, embeddings, RAG, similarity search, vector stores, prompting, structured output, or any AI provider (OpenAI, Anthropic, Gemini, Cohere, Groq, xAI, ElevenLabs, Jina, OpenRouter).

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

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->

```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

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

# Inertia v2

- Use all Inertia features from v1 and v2. Check the documentation before making changes to ensure the correct approach.
- New features: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

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

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.

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

=== laravel/ai rules ===

## Laravel AI SDK

- This application uses the Laravel AI SDK (`laravel/ai`) for all AI functionality.
- Activate the `developing-with-ai-sdk` skill when building, editing, updating, debugging, or testing AI agents, text generation, chat, streaming, structured output, tools, image generation, audio, transcription, embeddings, reranking, vector stores, files, conversation memory, or any AI provider integration (OpenAI, Anthropic, Gemini, Cohere, Groq, xAI, ElevenLabs, Jina, OpenRouter).

</laravel-boost-guidelines>
