---
name: saucebase-module-development
description: Create and develop Saucebase modules across Vue and React, including scaffolding, controllers, pages, migrations, service providers, Filament plugins, navigation, types, and tests. Use when working under modules/, running saucebase:recipe or modules:list, or changing module structure.
---

# Saucebase Module Development

Before writing code, run the pre-flight interview. Ask one question at a time.
Ask frontend-only questions only when the module has frontend pages.

---

## Pre-Flight Interview

**Q1. Does this module have frontend pages (Inertia with Vue and React) or is
it admin-only (Filament)?**
→ Answer gates the rest of the interview.

**Q2. Does it need models and migrations?**

**Q3. Does it need Filament resources?**

**Q4. *(if frontend)* Does it have public/SEO pages that need SSR?**

**Q5. Does it need a database seeder?**

**Q6. *(if frontend pages in the logged-in area)* Does it need navigation entries and breadcrumbs?**

**Q7. *(if frontend)* Should we write E2E tests?**

**Q8. Do you want to use TDD? If yes, activate `/tdd` before writing any implementation code.**

Once all answers are collected, proceed to scaffolding.

---

## Scaffolding

```bash
php artisan saucebase:recipe ModuleName
```

Choose **Basic Recipe** when prompted.

---

## Post-Scaffold Checklist

Run these after every scaffold, in order:

```bash
composer require saucebase/<module-name>
npm run build   # or restart `npm run dev`
```

Then apply answers from the pre-flight:

- **Admin-only module** → clear `routes/navigation.php` completely (never leave a `route()` call to a non-existent route)
- **No frontend pages** → delete scaffolded Vue and React pages and skip E2E setup
- **Frontend pages** → implement equivalent behavior in both framework source trees
- **Has seeder** → add `db:seed` task to `Taskfile.yml` (`php artisan modules:seed --module=<name>`)
- **Has E2E tests** → scaffold `tests/e2e/index.spec.ts` using `data-testid` selectors only
- **Uses TDD** → write failing tests before any implementation; activate `/tdd`
