---
name: saucebase-module-development
description: "Guides Saucebase module creation and development. Activate when scaffolding a new module, adding controllers/pages/migrations to a module, working with module service providers, Filament module plugins, or when user mentions saucebase:recipe, module:enable, or asks about module structure."
license: MIT
metadata:
  author: saucebase
---

# Saucebase Module Development

## When to Use

Activate this skill when:

- Creating a new Saucebase module from scratch
- Adding new files to an existing module (controllers, models, migrations, Filament resources)
- Scaffolding module boilerplate
- Working on module service providers, routes, navigation, or Vue pages

---

## Creating a New Module

### Primary Path: `saucebase:recipe`

Use the recipe command to scaffold a complete module from stubs:

```bash
php artisan saucebase:recipe ModuleName
```

The command prompts for a module name (PascalCase, no spaces or hyphens) and template selection. Choose **Basic Recipe** when prompted.

**What the Basic Recipe creates:**

```
modules/<modulename>/
  app/Providers/<Name>ServiceProvider.php
  app/Providers/RouteServiceProvider.php
  app/Http/Controllers/<Name>Controller.php
  app/Filament/<Name>Plugin.php
  config/config.php
  module.json
  routes/web.php, api.php, navigation.php
  resources/js/app.ts
  resources/js/pages/Index.vue
  resources/css/style.css
  tests/e2e/index.spec.ts
  Taskfile.yml, vite.config.js, package.json, composer.json
```

**Post-scaffold checklist:**

```bash
composer dump-autoload
php artisan module:enable ModuleName
php artisan module:migrate ModuleName --seed   # if migrations exist
npm run build   # or restart `npm run dev`
```

### After Scaffolding: Extend as Needed

Use `php artisan make:` commands to add more files to the module:

```bash
php artisan make:model --module=Feature -mf    # model + migration + factory
php artisan make:controller --module=Feature FeatureController
```

---

## Module Directory Structure

```
modules/<modulename>/
  app/
    Http/Controllers/
    Models/
    Providers/
      <Name>ServiceProvider.php      # Main — extends ModuleServiceProvider (REQUIRED)
      RouteServiceProvider.php       # Loads routes (REQUIRED if module has routes)
    Filament/
      <Name>Plugin.php               # Only if module has Filament resources
  config/
  database/
    migrations/
    seeders/
  lang/
    en/
    pt_BR/
  resources/
    js/
      pages/                         # Inertia pages
      components/
      app.ts                         # Module lifecycle hooks
      types/
        generated.d.ts               # Auto-generated — never edit manually
    css/
  routes/
    web.php
    api.php
    navigation.php                   # Frontend nav; leave empty if admin-only
    breadcrumbs.php
  tests/
    Feature/
    Unit/
    e2e/
  vite.config.js
  Taskfile.yml
  module.json
```

---

## Service Provider Pattern

Every module's main service provider must extend `App\Providers\ModuleServiceProvider` and define both `$name` and `$nameLower`. The base class auto-handles translations, config, migrations, and factories.

```php
namespace Modules\Feature\Providers;

use App\Providers\ModuleServiceProvider;

class FeatureServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Feature';
    protected string $nameLower = 'feature';

    protected array $providers = [
        RouteServiceProvider::class,
    ];

    // Optional: share data on every Inertia response
    protected function shareInertiaData(): void
    {
        \Inertia\Inertia::share([
            'feature.config' => fn () => config('feature'),
        ]);
    }
}
```

`module.json` `providers` array must reference **only the main service provider** (not RouteServiceProvider):

```json
{
    "providers": ["Modules\\Feature\\Providers\\FeatureServiceProvider"]
}
```

---

## Controller & Inertia Rendering

```php
namespace Modules\Feature\Http\Controllers;

use Inertia\Inertia;

class FeatureController
{
    public function index()
    {
        return Inertia::render('Feature::Index', [
            'items' => Item::all(),
        ])->withoutSSR();   // authenticated pages
    }

    public function landing()
    {
        return Inertia::render('Feature::Landing')->withSSR();  // public/SEO pages
    }
}
```

- `Feature::Index` resolves to `modules/feature/resources/js/pages/Index.vue`
- `.withSSR()` for public/SEO pages; `.withoutSSR()` for authenticated pages
- Default (no macro) = SSR disabled

---

## Vue Page Pattern

Pages use `AppLayout` and the shadcn-vue component library from `@/components/ui/`. All components **must** include dark mode variants.

```vue
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    items: Array<{ id: number; name: string }>
}>();
</script>

<template>
    <AppLayout title="Feature" :breadcrumbs="[{ title: 'Feature' }]">
        <div class="flex flex-1 flex-col gap-6 p-6 pt-2">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                Feature
            </h1>
            <p class="text-gray-600 dark:text-gray-400">Manage your items.</p>

            <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div v-for="item in items" :key="item.id" class="p-4">
                    <Link :href="route('feature.show', item.id)" class="font-medium text-primary/70 hover:underline underline-offset-4">
                        {{ item.name }}
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
```

**Required dark mode tokens:**

| Purpose | Classes |
|---|---|
| Background | `bg-white dark:bg-gray-900` or `bg-gray-50 dark:bg-gray-900` |
| Text primary | `text-gray-900 dark:text-white` |
| Text secondary | `text-gray-600 dark:text-gray-400` |
| Borders | `border-gray-200 dark:border-gray-800` |
| Links | `font-medium text-primary/70 hover:underline underline-offset-4` |

The shadcn-vue components (`Card`, `Button`, `Badge`, etc.) handle dark mode via CSS variables automatically — apply explicit `dark:` variants only for custom sections.

Use `$t('...')` for all user-facing strings (i18n via `laravel-vue-i18n`).

---

## Module Lifecycle Hooks (app.ts)

```typescript
import { registerIcon } from '@/lib/navigation';
import IconFeature from '~icons/heroicons/sparkles';

import '../css/style.css';

export function setup() {
    // Register the icon used in routes/navigation.php
    registerIcon('feature', IconFeature);
}

export function afterMount() {
    // DOM-dependent setup (CSR only)
}
```

`registerIcon(slug, component)` — slug must match the `icon` attribute in `navigation.php`.

---

## Routes & Navigation

**`routes/web.php`:**

```php
use Modules\Feature\Http\Controllers\FeatureController;
use Illuminate\Support\Facades\Route;

Route::resource('feature', FeatureController::class);
```

**`routes/navigation.php`** (frontend modules):

```php
use App\Facades\Navigation;
use App\Navigation\Section;

Navigation::add('Feature', route('feature.index'), function (Section $section) {
    $section->attributes([
        'group' => 'main',
        'slug'  => 'feature',
        'icon'  => 'feature',      // matches registerIcon() slug in app.ts
        'badge' => ['content' => 'New', 'variant' => 'info'],
    ]);
});
```

**Admin-only modules:** Leave `navigation.php` completely empty — never add a `route()` call pointing to a non-existent route.

---

## Filament Plugin Pattern

```php
namespace Modules\Feature\Filament;

use App\Filament\ModulePlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FeaturePlugin implements Plugin
{
    use ModulePlugin;

    public function getModuleName(): string { return 'Feature'; }

    public function getId(): string { return 'feature'; }

    public function boot(Panel $panel): void
    {
        // Register navigation groups, pages, etc.
    }
}
```

Auto-discovered by convention at `Modules\Feature\Filament\FeaturePlugin` — no manual registration needed.

---

## TypeScript Types

Annotate PHP enums and Spatie Data objects with `#[TypeScript]` to generate TS types:

```php
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum FeatureStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
```

Then regenerate:

```bash
php artisan module:generate-types Feature
```

Output: `modules/feature/resources/js/types/generated.d.ts` — **never edit this file manually**.

---

## Testing

```bash
# PHPUnit (single module)
php -d memory_limit=2048M artisan test --testsuite=Modules --filter='^Modules\Feature\Tests'

# E2E (single module)
npx playwright test --project="@Feature*"
```

Always use `data-testid` selectors — never select by translated text, labels, or role names:

```vue
<button data-testid="create-feature-btn">{{ $t('Create') }}</button>
```

```typescript
await page.getByTestId('create-feature-btn').click();
```

Item-specific testids: `{action}-${item.id}` (e.g. `data-testid="delete-${item.id}"`).

---

## Key Gotchas

- `RouteServiceProvider` **must** be listed in `$providers` in the main service provider, or routes won't load.
- `module.json` `providers` array must reference the **main service provider** (not RouteServiceProvider).
- `vite.config.js` uses **named export** (`export const paths = [...]`), not `module.exports` or `export default`.
- After enabling/disabling a module, always rebuild assets (`npm run build`) or restart `npm run dev`.
- Module types (`generated.d.ts`) are auto-generated — never edit them manually. Run `php artisan module:generate-types ModuleName` after changing PHP enums or Spatie Data objects.
- Always run PHPUnit with `php -d memory_limit=2048M` to avoid OOM errors in the Modules suite.
- `navigation.php` must be **empty** for admin-only modules (no frontend pages) — never add a `route()` call pointing to a non-existent route.
- `$name` and `$nameLower` are both required — the base `ModuleServiceProvider` throws a `LogicException` if either is missing.
