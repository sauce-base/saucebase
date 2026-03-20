# Saucebase Module Development

## When to Use

Activate this skill when:

- Creating a new Saucebase module from scratch
- Adding new files to an existing module (controllers, models, migrations, Filament resources)
- Scaffolding module boilerplate

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
modules/<ModuleName>/
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
modules/<ModuleName>/
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

## Key Gotchas

- `RouteServiceProvider` **must** be listed in `$providers` in the main service provider, or routes won't load.
- `module.json` `providers` array must reference the **main service provider** (not RouteServiceProvider).
- `vite.config.js` uses **named export** (`export const paths = [...]`), not `module.exports` or `export default`.
- After enabling/disabling a module, always rebuild assets (`npm run build`) or restart `npm run dev`.
- Module types (`generated.d.ts`) are auto-generated — never edit them manually. Run `php artisan module:generate-types ModuleName` after changing PHP enums or Spatie Data objects.
- Always run PHPUnit with `php -d memory_limit=2048M` to avoid OOM errors in the Modules suite.
- `navigation.php` must be **empty** for admin-only modules (no frontend pages) — never add a `route()` call pointing to a non-existent route.
