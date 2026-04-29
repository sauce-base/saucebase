## Saucebase

Saucebase is a modular Laravel SaaS starter kit (VILT stack). All features are encapsulated as **modules** under `modules/<modulename>/` (always lowercase). Modules are copy-and-own: once installed they live in the repo and can be edited freely.

### Module Creation

Use `php artisan saucebase:recipe {modulename}` to scaffold a new module from stubs. After scaffolding: `composer dump-autoload` → rebuild assets. There is no enable/disable toggle — a module is active when `composer require`-d.

### Module System

Modules are managed by `internachi/modular`. Module folder names are always lowercase (`modules/auth/`, `modules/billing/`). PHP namespaces remain TitleCase (`Modules\Auth\...`) per PSR-4. There is no `modules_statuses.json` — presence in `composer.json` determines active state.

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
