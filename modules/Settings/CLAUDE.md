# Settings Module

User profile management, avatar uploads, password changes, and application settings.

## Key Files

| Layer       | Files                                                                                                                                              |
| ----------- | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| Controllers | `ProfileController` (show, edit, updateInfo, updateAvatar, deleteAvatar), `PasswordController` (edit, update), `SettingsController` (index)        |
| Requests    | `UpdateProfileInfoRequest` (name, email), `UpdateProfileAvatarRequest` (image, max 2MB), `UpdatePasswordRequest` (current_password, confirmed new) |
| Filament    | `SettingsPlugin` (nav group), `GeneralSettings` page (placeholder)                                                                                 |
| Pages       | `Index`, `Profile`, `Profile/Edit`, `Profile/ChangePassword`                                                                                       |
| Layout      | Uses core `@/layouts/SettingsLayout.vue` (shared infrastructure for all settings pages)                                                            |
| Components  | `PageHeader` (module-specific)                                                                                                                     |

**No models or migrations** — this module operates on the core `User` model.

## Routes

All routes require `auth`, `verified`, `role:admin|user` middleware:

```
GET    /settings                     → settings.index
GET    /settings/profile             → settings.profile
GET    /settings/profile/edit        → settings.profile.edit
PATCH  /settings/profile/info        → settings.profile.update-info
POST   /settings/profile/avatar      → settings.profile.update-avatar
DELETE /settings/profile/avatar      → settings.profile.delete-avatar
GET    /settings/profile/password    → settings.profile.password.edit
PUT    /settings/profile/password    → settings.profile.password.update
```

## Patterns

### Avatar Management

Uses Spatie Media Library with a single-file `avatars` collection on the User model. Fallback chain in `User::getAvatarAttribute()`:

1. Media library upload (avatars collection)
2. Social provider avatar URL (database `avatar` field)
3. Default avatar (`images/default-avatar.jpg`)

Upload clears old collection before adding new: `clearMediaCollection('avatars')` then `addMediaFromRequest('avatar')->toMediaCollection('avatars')`.

### Responsive Layout

Settings pages use the core `SettingsLayout` from `@/layouts/SettingsLayout.vue`. This is shared infrastructure — any module can use it for its own settings pages (see core CLAUDE.md for the pattern).

### Social Account Display

Profile page shows connected providers with connect/disconnect buttons. Delegates entirely to Auth module:

- Connect: links to `route('auth.socialite.redirect', provider.name)`
- Disconnect: DELETE to `route('auth.socialite.disconnect', provider)`
- Data: `$user->connected_providers` from Auth module's `Sociable` trait

### Password Change

`PasswordController::update()` hashes new password, saves, sends `PasswordChangedNotification` (mail channel with timestamp and profile link), then redirects with toast.

### Navigation

Configured in `routes/navigation.php` via Spatie Navigation. Three groups:

- `user` — "Settings" link (order 10)
- `settings` — "General" (10) and "Profile" (20) in sidebar
- `secondary` — "Settings" with destructive badge

Other modules can add items to the `settings` group from their own `routes/navigation.php`. Frontend reads from `page.props.navigation?.settings`.

## Testing

```bash
php artisan test --testsuite=Modules --filter=Settings  # PHPUnit
npx playwright test --project=@Settings                 # E2E
```

E2E tests in `tests/e2e/index.spec.ts` — basic settings page accessibility.

## Gotchas

- No own models — depends on core `User` model and Auth module's `Sociable` trait
- Social connect/disconnect routes belong to the Auth module, not Settings
- Avatar deletion only removes the Media Library upload — social avatar URL remains as fallback
- All routes require `verified` middleware — unverified users can't access settings
- `PasswordChangedNotification` lives in `app/Notifications/` (core), not in this module
