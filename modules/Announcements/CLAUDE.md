# Announcements Module

Site-wide announcement banners with scheduling, audience targeting, and dismissal support.

## Key Files

| Layer | Files |
|-------|-------|
| Controller | `DismissAnnouncementController` — sets dismiss cookie, returns back |
| Model | `Announcement` (text, is_active, is_dismissable, show_on_frontend, show_on_dashboard, starts_at, ends_at, created_by) |
| Provider | `AnnouncementsServiceProvider` — extends `ModuleServiceProvider`, shares active announcement as Inertia prop via `shareInertiaData()` |
| Filament | `AnnouncementsPlugin`, `AnnouncementResource` (List, Create, Edit), `AnnouncementForm`, `AnnouncementsTable` |
| Component | `AnnouncementBanner.vue` — sticky banner rendered in core `App.vue` |
| Types | `resources/js/types/index.ts` — `Announcement` interface |

## Routes

```
POST /announcements/{announcement}/dismiss  → announcements.dismiss  (web)
```

No frontend pages — admin-only via Filament.

## Patterns

### Active Scope
`Announcement::active()` scope filters by: `is_active = true`, within `starts_at`/`ends_at` window (nulls mean open-ended), ordered by latest first.

### Inertia Prop Sharing
`AnnouncementsServiceProvider::shareInertiaData()` shares the active announcement as `announcement` on every Inertia response. Returns `null` if:
- No active announcement exists
- The cookie `saucebase_announcement_dismissed` matches the announcement ID

Only shares: `id`, `text`, `is_dismissable`, `show_on_frontend`, `show_on_dashboard`.

### Dismissal
`DismissAnnouncementController` sets a 1-year cookie (`saucebase_announcement_dismissed`) with the announcement ID. The banner component posts to `announcements.dismiss` on click; the provider checks the cookie on subsequent requests.

Cookie name is configurable via `config('announcements.cookie_name')`.

### Audience Targeting
Two boolean flags on the model:
- `show_on_frontend` — show on public (unauthenticated) pages
- `show_on_dashboard` — show on authenticated pages

`AnnouncementBanner.vue` checks the authenticated state and the appropriate flag before rendering.

### Creator Tracking
`CreateAnnouncement::mutateFormDataBeforeCreate()` sets `created_by = auth()->id()` automatically.

### Core Integration
The module ships one patch that must be applied to the core app:

| Patch | Change |
|-------|--------|
| `patches/app-vue.patch` | Imports `AnnouncementBanner` and adds it to `App.vue` template |

The `announcement` TypeScript type is handled automatically via `resources/js/types/page-props.d.ts`, which augments `@inertiajs/vue3`'s `PageProps` — no manual core edit required.

## Configuration

```php
// config/config.php
'cookie_name' => 'saucebase_announcement_dismissed',
```

## Testing

```bash
php artisan test --testsuite=Modules --filter='^Modules\\Announcements\\Tests'  # PHPUnit
npx playwright test --project="@Announcements*"                         # E2E
```

**Unit coverage** (`tests/Unit/AnnouncementTest.php`): `active()` scope — inactive, no schedule, future starts_at, past ends_at, within window, multiple active.

**Feature coverage** (`tests/Feature/AnnouncementResourceTest.php`): Filament CRUD, Inertia prop sharing, dismissed cookie suppression, dismiss route sets cookie.

**E2E coverage** (`tests/e2e/tests/announcement.spec.ts`): banner visibility on public/dashboard, dismiss persists across reload, no dismiss button when non-dismissable, schedule window (with time travel).

## Gotchas

- No frontend routes or pages — this module is admin-only; the banner is rendered by core `App.vue`
- Only `app-vue.patch` is required for the banner to appear; without it the component is never mounted. TypeScript types are automatic via `page-props.d.ts`
- Cookie is set for 1 year (60 × 24 × 365 minutes); users (or the app) can clear/overwrite it to re-show the same announcement, but if you want to re-show to users who dismissed it without touching cookies, publish a new announcement (new ID)
- `show_on_frontend` / `show_on_dashboard` are independent — an announcement can target one or both audiences
