# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Saucebase is a modular Laravel SaaS starter kit built on the VILT stack (Vue 3, Inertia.js, Laravel 13, Tailwind CSS 4). It follows a **copy-and-own philosophy** where modules are installed directly into the repository rather than being maintained as external packages. This is a Docker-first setup with hot reload, TypeScript, and built-in best practices.

**Key Technologies:**

- Backend: Laravel 13, PHP 8.4+, Filament 5 admin panel
- Frontend: Vue 3 Composition API, TypeScript 5.8, Inertia.js 3.0, Tailwind CSS 4
- Build: Vite 6.4 with HMR, SSR support
- Testing: PHPUnit (backend), Playwright (E2E)
- Code Quality: PHPStan level 9, Laravel Pint, ESLint, Prettier
- Infrastructure: Docker (Nginx, MySQL 8, Redis, Mailpit)

## Common Commands

### Development

```bash
# Start development environment (recommended)
composer dev
# Runs: Laravel dev server, queue worker, Pail logs, and Vite dev server in parallel

# Alternative: Individual services
php artisan serve                    # Start Laravel dev server
npm run dev                          # Start Vite dev server with HMR
php artisan queue:listen --tries=1   # Start queue worker
php artisan pail --timeout=0         # Monitor logs in real-time
```

### Docker Operations

```bash
# Start all services
docker compose up -d --wait

# Execute commands in app container
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app composer install

# Restart services
docker compose restart app

# View logs
docker compose logs -f app
docker compose logs -f nginx
```

### Installation & Setup

```bash
# Quick install (recommended for new projects)
php artisan saucebase:install

# Advanced options
php artisan saucebase:install --no-docker    # Skip Docker setup
php artisan saucebase:install --no-ssl       # Skip SSL generation
php artisan saucebase:install --force        # Force reinstallation
php artisan saucebase:install --no-interaction  # CI/CD mode
```

### Module Management

**Important**: Modules are Composer packages that get installed directly into your repository. They are not external dependencies but code you own and can customize.

#### Available Official Modules

| Module       | Description                                               | Repository                                                          |
| ------------ | --------------------------------------------------------- | ------------------------------------------------------------------- |
| **Auth**     | Authentication with social login support (Google, GitHub) | [saucebase-dev/auth](https://github.com/saucebase-dev/auth)         |
| **Settings** | Settings management module                                | [saucebase-dev/settings](https://github.com/saucebase-dev/settings) |

#### Installation Steps

To install a module, follow these steps in order:

```bash
# 1. Install the module via Composer
composer require saucebase/auth

# For development-only modules, use --dev flag
# composer require --dev saucebase/module-name

# 2. Regenerate autoload files
composer dump-autoload

# 3. Enable the module
docker compose exec app php artisan module:enable Auth

# 4. Run migrations and seeders
docker compose exec app php artisan module:migrate Auth --seed

# 5. Build frontend assets to include module resources
npm run build
```

**What each command does:**

1. `composer require` - Downloads the module package and adds it to composer.json
2. `composer dump-autoload` - Regenerates Composer's autoload files to include new module classes
3. `module:enable` - Marks the module as enabled in `modules_statuses.json`
4. `module:migrate --seed` - Runs database migrations and seeds module data
5. `npm run build` - Rebuilds frontend assets to include module JavaScript/CSS

**Alternative for local development (without Docker):**

```bash
composer require saucebase/auth
composer dump-autoload
php artisan module:enable Auth
php artisan module:migrate Auth --seed
npm run build
```

#### Managing Installed Modules

```bash
# Enable/disable modules
php artisan module:enable Auth
php artisan module:disable Auth

# Run module-specific operations
php artisan module:migrate Auth         # Run module migrations
php artisan module:migrate-refresh Auth # Refresh module migrations
php artisan module:seed Auth            # Seed module data
php artisan module:list                 # List all modules

# Inside Docker
docker compose exec app php artisan module:list
```

**Important:** After enabling/disabling modules, rebuild frontend assets with `npm run build` or restart `npm run dev`.

#### Example: Installing Auth Module

```bash
# Install the package
composer require saucebase/auth
composer dump-autoload

# Enable and migrate
docker compose exec app php artisan module:enable Auth
docker compose exec app php artisan module:migrate Auth --seed

# Build assets
npm run build

# Configure OAuth (optional)
# Add to .env:
# GOOGLE_CLIENT_ID=your-client-id
# GOOGLE_CLIENT_SECRET=your-client-secret
# GITHUB_CLIENT_ID=your-client-id
# GITHUB_CLIENT_SECRET=your-client-secret
```

**The Auth module provides:**

- Login, registration, password reset flows
- OAuth integration (Google, GitHub via Laravel Socialite)
- Multiple provider connections per user
- Routes: `/auth/login`, `/auth/register`, `/auth/forgot-password`
- Admin panel access at `/admin` (credentials: `chef@saucebase.dev` / `secretsauce`)

### Testing

```bash
# Backend tests (PHPUnit)
php artisan test                    # Run all tests
php artisan test --filter TestName  # Run specific test
php artisan test tests/Feature      # Run feature tests only
php artisan test tests/Unit         # Run unit tests only
php artisan test --testsuite=Modules  # Run module tests only

# E2E tests (Playwright)
npm run test:e2e                   # Run all E2E tests
npm run test:e2e:ui                # Open Playwright UI
npm run test:e2e:headed            # Run tests in headed mode
npm run test:e2e:debug             # Debug tests
npm run test:e2e:report            # View test report
```

### Code Quality

```bash
# PHP
composer analyse                   # Run PHPStan analysis
composer lint                      # Run Laravel Pint formatter
vendor/bin/pint                    # Format PHP code
vendor/bin/phpstan analyse --memory-limit=2G  # Static analysis

# JavaScript/TypeScript
npm run lint                       # Run ESLint with auto-fix
npm run format                     # Format with Prettier
npm run format:check               # Check formatting
```

### Asset Building

```bash
# Development
npm run dev                        # Start Vite dev server with HMR

# Production
npm run build                      # Build for production (includes SSR)
npm run build:ssr                  # Explicitly build with SSR
npm run preview                    # Preview production build
```

### Database

```bash
# Migrations
php artisan migrate                      # Run migrations
php artisan migrate:fresh --seed         # Fresh migration with seeding
php artisan migrate:status               # Check migration status
php artisan migrate:rollback             # Rollback last migration

# Seeders
php artisan db:seed                      # Run DatabaseSeeder
php artisan db:seed --class=RolesDatabaseSeeder  # Run specific seeder
```

### Cache & Optimization

```bash
# Clear caches
php artisan optimize:clear         # Clear all caches
php artisan config:clear           # Clear config cache
php artisan route:clear            # Clear route cache
php artisan view:clear             # Clear view cache

# Optimize for production
php artisan optimize               # Cache config, routes, views
php artisan config:cache           # Cache configuration
php artisan route:cache            # Cache routes
php artisan view:cache             # Compile views
```

## Architecture

### Modular Structure

Saucebase uses **nwidart/laravel-modules** for module management. Modules are self-contained feature packs that can be installed, enabled, or disabled independently.

```
modules/
├── <ModuleName>/
│   ├── app/                    # Module backend code
│   │   ├── Http/Controllers/
│   │   ├── Models/
│   │   ├── Providers/
│   │   └── ...
│   ├── config/                 # Module configuration
│   ├── database/
│   │   ├── migrations/
│   │   ├── seeders/
│   │   └── factories/
│   ├── lang/                   # Module translations
│   ├── resources/
│   │   ├── js/                # Vue components, pages
│   │   │   ├── app.ts         # Module setup (optional)
│   │   │   ├── pages/         # Inertia pages
│   │   │   └── components/    # Vue components
│   │   └── css/               # Module styles
│   ├── routes/
│   │   ├── web.php
│   │   └── api.php
│   ├── tests/
│   │   ├── Feature/
│   │   ├── Unit/
│   │   └── e2e/               # Playwright tests
│   ├── vite.config.js         # Module asset paths
│   ├── playwright.config.ts   # Module E2E config (optional)
│   └── module.json            # Module metadata
```

**Module Discovery:**

- Modules are tracked in `modules_statuses.json` (format: `{"ModuleName": true}`)
- Only enabled modules are loaded and built
- The `module-loader.js` automatically discovers and collects enabled module assets, translations, and Playwright configs

### Frontend Architecture

**Inertia SPA with Module Support:**

The frontend uses a custom module resolution system that allows pages to be loaded from modules using namespace syntax:

```typescript
// In routes: render module pages like this
return inertia('Auth::Login', ['data' => $data]);

// Resolves to: modules/auth/resources/js/pages/Login.vue
```

**Key Frontend Files:**

- `resources/js/app.ts` - Main Inertia app entry point
- `resources/js/ssr.ts` - SSR entry point
- `resources/js/lib/utils.ts` - Contains `resolveModularPageComponent()` for module page resolution
- `resources/js/lib/moduleSetup.ts` - Module lifecycle management (setup, afterMount)

**Module Lifecycle:**

Modules can export setup hooks in `modules/<Name>/resources/js/app.ts`:

```typescript
export default {
    setup(app) {
        // Called before Vue app mounts
        // Register plugins, components, etc.
    },
    afterMount(app) {
        // Called after Vue app mounts
        // Initialize services that require DOM
    },
};
```

**Component Library:**

Uses shadcn-vue style components in `resources/js/components/ui/` with Tailwind CSS 4. Components follow the copy-and-own pattern and can be customized directly.

### Backend Architecture

**Service Providers:**

- `AppServiceProvider` - Core app configuration, HTTPS enforcement, fixes module event discovery
- `ModuleServiceProvider` (abstract) - Base class for module service providers, handles translations, config, migrations, and Inertia data sharing
- `NavigationServiceProvider` - Spatie navigation configuration
- `BreadcrumbServiceProvider` - Diglactic breadcrumbs setup
- `FilamentServiceProvider` - Filament admin panel configuration

**Module Service Providers:**

All modules must extend `App\Providers\ModuleServiceProvider` and define `$name` and `$nameLower` properties:

```php
class AuthServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Auth';
    protected string $nameLower = 'auth';

    protected array $commands = [
        // Command classes
    ];

    protected array $providers = [
        // Additional service providers
    ];
}
```

**Helpers:**

Global helper functions are auto-loaded from `app/Helpers/helpers.php` (currently empty, but available for project-wide utilities).

### Asset Pipeline

**Vite Configuration (`vite.config.js`):**

- Dynamically loads SSL certificates if present (`docker/ssl/app.pem`, `docker/ssl/app.key.pem`)
- Uses `module-loader.js` to collect language paths from enabled modules
- Provides path aliases: `@` → `resources/js`, `@modules` → `modules/`, `ziggy-js` → vendor path

**Module Asset Collection:**

The `module-loader.js` provides functions to collect module resources:

- `collectModuleAssetsPaths()` - Collect asset paths from module `vite.config.js` files
- `collectModuleLangPaths()` - Collect translation directories
- `collectModulePlaywrightConfigs()` - Collect E2E test projects

Each module can export a `vite.config.js` with a `paths` array:

```javascript
export default {
    paths: ['css/app.css', 'js/app.ts'], // Relative to modules/<Name>/resources/
};
```

### Multi-Tenancy Support

SSL certificates are generated with wildcard support (`*.localhost`) to enable multi-tenant applications. The infrastructure is ready for packages like Spatie Laravel Multitenancy or Tenancy for Laravel.

**Supported domains:**

- `https://localhost` - Main application
- `https://*.localhost` - Any subdomain works with the wildcard certificate

### Environment Configuration

**Saucebase-specific variables:**

- `APP_HOST` - Application hostname (default: `localhost`)
- `APP_URL` - Full URL, must match APP_HOST (default: `https://localhost`)
- `APP_SLUG` - Project slug for storage/database keys (default: `saucebase`)

**HTTPS Configuration:**

The `AppServiceProvider` automatically enforces HTTPS in production/staging and respects local SSL setup. SSL enforcement includes:

- URL generation forced to HTTPS
- Security headers (HSTS, CSP upgrade-insecure-requests, X-Content-Type-Options)

### Testing Architecture

**PHPUnit (`phpunit.xml`):**

Three test suites configured:

- `Unit` - `tests/Unit/`
- `Feature` - `tests/Feature/`
- `Modules` - `modules/*/tests/Feature/` and `modules/*/tests/Unit/`

Tests run with SQLite in-memory database by default.

**Playwright (`playwright.config.ts`):**

- Automatically discovers E2E tests from enabled modules via `module-loader.js`
- Creates test projects for each module prefixed with `@ModuleName`
- Core tests in `tests/e2e/` run as `@Core`
- Each project runs across selected devices (default: Desktop Chrome)
- Vite dev server started automatically in local environment (not CI)

## Git Workflow & Commit Standards

### Commit Message Format

This project enforces strict commit message standards using Commitlint with Husky hooks.

**Format:**

```
type(scope): subject
```

or

```
type: subject
```

**Rules:**

- **Single-line commits only** - No body or footer allowed
- Maximum header length: 150 characters
- Type: required, must be lowercase
- Scope: optional, must be lowercase
- Subject: required, must be lowercase (cannot start with capital letter)

### Allowed Commit Types

| Type       | Description                                               | Example                                         |
| ---------- | --------------------------------------------------------- | ----------------------------------------------- |
| `feat`     | A new feature                                             | `feat(auth): add social login support`          |
| `fix`      | A bug fix                                                 | `fix(dashboard): resolve chart rendering issue` |
| `docs`     | Documentation only changes                                | `docs: update installation guide`               |
| `style`    | Code style changes (formatting, missing semicolons, etc.) | `style: format components with prettier`        |
| `refactor` | Code changes that neither fix bugs nor add features       | `refactor(api): simplify error handling logic`  |
| `perf`     | Performance improvements                                  | `perf(queries): optimize database queries`      |
| `test`     | Adding or correcting tests                                | `test(auth): add login validation tests`        |
| `chore`    | Build process or tooling changes                          | `chore: update dependencies`                    |
| `ci`       | CI configuration changes                                  | `ci: add playwright workflow`                   |
| `build`    | Build system or external dependency changes               | `build: upgrade vite to 6.4`                    |
| `revert`   | Reverts a previous commit                                 | `revert: revert feat(auth): add social login`   |

### Commit Examples

**Valid commits:**

```bash
feat: add user authentication module
fix(api): resolve timeout issue in user endpoint
docs: update readme with docker instructions
refactor: simplify module loader logic
test(e2e): add playwright tests for login flow
chore(deps): upgrade laravel to 12.0
```

**Invalid commits (will be rejected):**

```bash
# ❌ Type must be lowercase
Feat: add new feature

# ❌ Subject cannot start with capital letter
feat: Add new feature

# ❌ Invalid type
feature: add new feature

# ❌ Type cannot be empty
add new feature

# ❌ Body/footer not allowed (single-line only)
feat: add new feature

This adds a new feature for users
```

### Pre-commit Hooks

Husky automatically runs these checks before each commit:

**PHP Formatting:**

- `composer lint` - Runs Laravel Pint to auto-format PHP code

**JavaScript/TypeScript/Vue Formatting:**

- `npx lint-staged` - Runs ESLint and Prettier on staged files
- Affected files: `**/*.{js,ts,vue}`
- Auto-fixes and formats code before commit

### Manual Validation

```bash
# Run linters manually
composer lint          # PHP
npm run lint          # JavaScript/TypeScript
npm run format        # Prettier
```

## Development Principles & Coding Standards

### Core Principles

This project follows industry best practices for clean, maintainable code:

**DRY (Don't Repeat Yourself)**

- Extract common logic into reusable functions, classes, or composables
- Abstract when the same logic appears 3+ times
- Example: Use composables for shared Vue logic, service classes for shared backend logic

**KISS (Keep It Simple, Stupid)**

- Prefer simple, obvious solutions over clever ones
- Write code that others can understand at a glance
- Avoid premature optimization

**YAGNI (You Aren't Gonna Need It)**

- Don't build features for hypothetical future requirements
- Implement only what's needed now
- Refactor when requirements actually change

**Single Responsibility Principle**

- Each class/function should do one thing well
- Controllers handle HTTP requests, services contain business logic
- Components focus on presentation, composables manage state/logic

**Separation of Concerns**

- Backend: Controllers → Services → Models
- Frontend: Pages → Components → Composables → Utils

### Code Quality Standards

#### PHP/Laravel Standards

**Enforced by Tools:**

- PHPStan level 5 static analysis (run: `composer analyse`)
- Laravel Pint PSR-12 formatting (run: `composer lint`)
- Pre-commit hooks ensure compliance

**Required Practices:**

```php
// ✅ Good: Type hints, PHPDoc, clear method names
/**
 * Retrieve active users with their roles.
 *
 * @return \Illuminate\Database\Eloquent\Collection<int, User>
 */
public function getActiveUsers(): Collection
{
    return User::with('roles')
        ->where('active', true)
        ->get();
}

// ❌ Bad: No types, unclear name, missing docs
public function getUsers()
{
    return User::where('active', true)->get();
}
```

**Class Structure:**

- Max 200-300 lines per class (if larger, consider splitting)
- Max 20-30 lines per method
- Use service classes for complex business logic
- Keep controllers thin (validate input, call service, return response)

#### JavaScript/TypeScript Standards

**Enforced by Tools:**

- ESLint with Vue + TypeScript rules (run: `npm run lint`)
- Prettier formatting (run: `npm run format`)
- Pre-commit hooks ensure compliance

**Required Practices:**

```typescript
// ✅ Good: TypeScript types, composables, clear structure
<script setup lang="ts">
import { ref, computed } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    users: User[];
}>();

const activeUsers = computed(() =>
    props.users.filter(u => u.active)
);
</script>

// ❌ Bad: No types, unclear logic
<script setup>
const props = defineProps(['users']);
const filtered = props.users.filter(u => u.active);
</script>
```

### When to Abstract vs When to Keep Simple

**✅ Create Abstractions When:**

- Same logic appears **3+ times**
- Clear reusability across multiple contexts
- Well-defined interface/contract
- Logic is complex enough to warrant isolation

**Examples:**

```php
// ✅ Good abstraction: Reusable service
class UserNotificationService {
    public function notifyPasswordChanged(User $user): void
    {
        $user->notify(new PasswordChangedNotification());
    }
}

// ✅ Good abstraction: Composable for shared state
// useLocalization.ts
export function useLocalization() {
    const language = ref(loadStoredLanguage());
    return { language, setLanguage };
}
```

**❌ Don't Create Abstractions When:**

- Logic used only **once** or **twice**
- Abstraction makes code harder to understand
- Building for hypothetical future needs
- Simple inline code is clearer

**Examples:**

```php
// ❌ Bad: Over-engineered for one-time use
class StringUppercaseTransformer {
    public function transform(string $input): string {
        return strtoupper($input);
    }
}

// ✅ Good: Simple inline operation
$name = strtoupper($user->name);
```

### Security Best Practices

**OWASP Top 10 Awareness:**

1. **SQL Injection Prevention**
    - ✅ Use Eloquent ORM or query builder
    - ✅ Use parameter binding
    - ❌ Never concatenate user input into queries

2. **XSS Prevention**
    - ✅ Vue automatically escapes template output
    - ✅ Use `v-html` only with sanitized content
    - ❌ Never trust user input in HTML

3. **CSRF Protection**
    - ✅ Laravel CSRF middleware enabled by default
    - ✅ `@csrf` directive in forms

4. **Command Injection**
    - ❌ Avoid `exec()`, `shell_exec()`, `system()` with user input
    - ✅ Use Laravel's process handling if needed

5. **Authentication/Authorization**
    - ✅ Use Laravel's built-in auth system
    - ✅ Check permissions with gates/policies
    - ✅ Validate ownership before modifying resources

6. **Sensitive Data**
    - ❌ Never commit `.env` files or API keys
    - ✅ Use environment variables
    - ✅ Add secrets to `.gitignore`

### Readability & Maintainability

**Naming Conventions:**

```php
// ✅ Good: Descriptive, self-documenting
$activeSubscriptionUsers = User::whereHas('subscription', fn($q) =>
    $q->where('status', 'active')
)->get();

// ❌ Bad: Unclear abbreviations
$asUsers = User::whereHas('sub', fn($q) => $q->where('s', 'a'))->get();
```

**Function Length:**

- Ideal: 10-20 lines
- Maximum: 30-40 lines
- If longer, break into smaller functions

**Nesting Depth:**

- Maximum: 3-4 levels deep
- Use early returns to reduce nesting

```php
// ✅ Good: Early returns, flat structure
public function process(User $user): bool
{
    if (!$user->isActive()) {
        return false;
    }

    if (!$user->hasPermission('process')) {
        return false;
    }

    return $this->performProcess($user);
}

// ❌ Bad: Deep nesting
public function process(User $user): bool
{
    if ($user->isActive()) {
        if ($user->hasPermission('process')) {
            return $this->performProcess($user);
        }
    }
    return false;
}
```

### Testing Standards

**Required Tests:**

- Feature tests for user-facing workflows
- Unit tests for complex business logic
- E2E tests for critical user paths (auth, checkout, etc.)

**Test Organization:**

```php
// ✅ Good: Clear test structure
/** @test */
public function it_creates_user_with_valid_data(): void
{
    // Arrange
    $data = ['name' => 'John', 'email' => 'john@example.com'];

    // Act
    $user = User::create($data);

    // Assert
    $this->assertDatabaseHas('users', $data);
}
```

**Coverage Expectations:**

- Critical business logic: 80%+ coverage
- Service classes: 70%+ coverage
- Controllers: Feature tests over unit tests

### Performance Guidelines

**Database Optimization:**

```php
// ❌ Bad: N+1 query problem
$users = User::all();
foreach ($users as $user) {
    echo $user->posts->count(); // Queries for each user
}

// ✅ Good: Eager loading
$users = User::withCount('posts')->get();
foreach ($users as $user) {
    echo $user->posts_count; // Single query
}
```

**Caching Strategies:**

- Use Redis for session/cache (already configured)
- Cache expensive queries: `Cache::remember('key', $ttl, fn() => ...)`
- Clear cache after updates: `Cache::forget('key')`

**Frontend Performance:**

- Lazy load heavy components: `defineAsyncComponent()`
- Use `v-show` for frequent toggles, `v-if` for conditional rendering
- Optimize images: use `loading="lazy"` attribute

### Error Handling

**Fail Fast Principle:**

```php
// ✅ Good: Validate early, fail fast
public function updateProfile(User $user, array $data): void
{
    if (!isset($data['email'])) {
        throw new InvalidArgumentException('Email is required');
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Invalid email format');
    }

    $user->update($data);
}
```

**Exception Handling:**

- Use specific exception types
- Log errors for debugging: `Log::error($message, $context)`
- Return user-friendly messages to frontend
- Don't catch exceptions you can't handle

### Code Review Checklist

Before requesting review, ensure:

- [ ] Code runs without errors
- [ ] All tests pass (`php artisan test`, `npm run test:e2e`)
- [ ] Static analysis passes (`composer analyse`)
- [ ] Code is formatted (`composer lint`, `npm run format`)
- [ ] No security vulnerabilities introduced
- [ ] No N+1 queries or performance issues
- [ ] PHPDoc/JSDoc added for public methods
- [ ] Commit messages follow conventional format
- [ ] No sensitive data in commits
- [ ] Frontend assets built (`npm run build`)

## Known Patterns & Conventions

### Inertia Page Resolution

Pages can be rendered from core or modules:

```php
// Core pages
return inertia('Dashboard');  // resources/js/pages/Dashboard.vue

// Module pages (namespace syntax)
return inertia('Auth::Login');  // modules/auth/resources/js/pages/Login.vue
return inertia('Settings::Index');  // modules/settings/resources/js/pages/Index.vue
```

### Translations

- Core translations: `lang/` (Portuguese and English by default)
- Module translations: `modules/<Name>/lang/`
- Frontend: `laravel-vue-i18n` with async loading
- Backend: Laravel's translation system with module support

### Navigation

Uses Spatie Laravel Navigation for menu management. Configure in `NavigationServiceProvider`.

### Permissions

Uses Spatie Laravel Permission for role/permission management. Default roles seeded:

- Admin role (seeded via `RolesDatabaseSeeder`)
- User role (seeded via `RolesDatabaseSeeder`)

Default middleware checks: `role:admin|user`

### Admin Panel

Filament 4 admin panel available at `/admin`. After installing the Auth module, default credentials:

- Email: `chef@saucebase.dev`
- Password: `secretsauce`

### Email Testing

Mailpit available at `http://localhost:8025` for viewing sent emails during development.

## Troubleshooting

### Module Not Found Errors

1. Check `modules_statuses.json` - ensure module is enabled (`true`)
2. Run `composer dump-autoload`
3. Clear caches: `php artisan optimize:clear`
4. Rebuild frontend: `npm run build` or restart `npm run dev`

### Frontend Build Issues

```bash
# Clear Laravel caches
php artisan optimize:clear

# Reinstall Node modules
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Port Conflicts

Modify `.env` to change default ports:

```env
APP_PORT=8080                    # Default: 80
APP_HTTPS_PORT=8443              # Default: 443
FORWARD_DB_PORT=33060            # Default: 3306
FORWARD_REDIS_PORT=63790         # Default: 6379
```

Then restart: `docker compose down && docker compose up -d`

### Database Connection Issues

Wait for MySQL to be ready (10-30 seconds on first start):

```bash
docker compose up -d --wait
docker compose ps mysql
docker compose logs mysql
```

## Important Notes

- Always rebuild frontend assets after enabling/disabling modules
- Module routes are automatically loaded from `modules/*/routes/web.php` and `modules/*/routes/api.php`
- Module migrations are auto-discovered when modules are enabled
- The `module-loader.js` handles all module asset discovery - don't bypass it
- SSL certificates support wildcard domains for multi-tenancy out of the box
- Xdebug is available in Docker with `XDEBUG_MODE=debug` (default in `.env`)

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
