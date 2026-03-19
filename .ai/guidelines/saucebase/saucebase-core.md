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
php -d memory_limit=2048M artisan test --testsuite=Modules --filter='^Modules\\FeatureName\\Tests'

# E2E — single module
npx playwright test --project="@FeatureName*"
```

Always run with `php -d memory_limit=2048M` to avoid OOM errors in the Modules suite.
