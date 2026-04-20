# Blog Module

## Overview

Full-featured blog with posts, categories, cover images, author attribution, and SEO metadata. Public-facing pages are SSR-enabled. Filament admin at `/admin` → Blog section.

- **Composer alias:** `blog`
- **Enabled by default:** yes

---

## Non-Obvious Design

### Published Scope

`scopePublished()` requires **both** conditions: `status = Published` AND (`published_at IS NULL` OR `published_at <= now()`). A post with status Published but a future `published_at` is not yet live.

### Dual-Slug Routing

Two routes resolve a post — with and without category prefix:
- `/blog/{slug}` → `blog.show`
- `/blog/{category}/{slug}` → `blog.show.category`

`BlogController::show()` accepts both and resolves the post by slug alone regardless of which route matched.

### PHP → TypeScript Serialization Contract

`BlogController::serializePost()` is the contract between backend and frontend. Both sides must stay in sync when fields change. Shape:

```php
[
    'id'           => int,
    'title'        => string,
    'slug'         => string,
    'excerpt'      => string|null,
    'cover_url'    => string,          // getFirstMediaUrl('cover')
    'published_at' => string|null,     // ->toDateString() e.g. "2025-04-19"
    'category'     => ['name' => string, 'slug' => string] | null,
    'author'       => ['name' => string, 'avatar_url' => string] | null,
    'url'          => string,          // route('blog.show') or route('blog.show.category')
]
```

`Show` page additionally receives `'content' => string` (full HTML).

When adding a field visible on the frontend, update **all three** in sync:
1. Migration + model `$fillable`
2. `serializePost()` output array
3. `resources/js/types/index.ts` `Post` interface

---

## Testing

```bash
# PHPUnit — this module only
php -d memory_limit=2048M artisan test --testsuite=Modules --filter='^Modules\\Blog\\Tests'

# E2E
npx playwright test --project="@Blog*"
```

---

## Known Issues

| Issue | Location | Fix |
|-------|----------|-----|
| Workflow name is "Announcements Module Tests" | `.github/workflows/test.yml` line 1 | Change to `Blog Module Tests` |
| Workflow `module:` is `Announcements` | `.github/workflows/test.yml` line 16 | Change to `Blog` |
| E2E test selects `[data-testid^="post-link-"]` | `tests/e2e/blog.spec.ts` | Testid removed — update to use `post-card-{id}` click |
| Several pt_BR keys untranslated | `lang/pt_BR.json` | `"From the blog"`, `"View all"`, `"Next →"` |
