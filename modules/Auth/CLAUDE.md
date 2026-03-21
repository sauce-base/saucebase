# Auth Module

Authentication, registration, magic link (passwordless), password reset, email verification, OAuth (Socialite), and user impersonation.

## Key Files

| Layer | Files |
|-------|-------|
| Controllers | `LoginController`, `RegisterController`, `SocialiteController`, `ForgotPasswordController`, `ResetPasswordController`, `VerifyEmailController`, `EmailVerificationNotificationController`, `EmailVerificationPromptController`, `PasswordController`, `ReimpersonateController`, `MagicLinkController` |
| Models | `SocialAccount` (provider, tokens, avatar, last_login_at), `MagicLinkToken` (hashed token, expires_at, used_at) |
| Service | `SocialiteService` — all OAuth logic (find/create user, link/disconnect accounts) |
| Requests | `LoginRequest` (credential validation + rate limiting), `RegisterRequest` (password hashing in `passedValidation`) |
| Exceptions | `AuthException` (credentials, throttle), `SocialiteException` (disconnect, account linking, provider validation) |
| Listeners | `AssignUserRole` (Registered), `UpdateUserLastLogin` (Login), `Impersonation` (TakeImpersonation — session history) |
| Notifications | `WelcomeNotification` (Registered), `MagicLinkNotification` (passwordless login link, 15-min expiry) |
| Trait | `Sociable` — added to User model (socialAccounts relation, connected_providers, disconnect) |
| Filament | `AuthPlugin`, `UserResource` (list, create, view, edit), `UserForm`, `UsersTable` |
| Pages | `Login`, `Register`, `ForgotPassword`, `ResetPassword`, `VerifyEmail`, `MagicLink` |
| Layout | `AuthCardLayout` — card with logo, status alerts, page transitions |
| Component | `SocialiteProviders` — Google/GitHub buttons with divider |

## Routes

**Guest routes** (`/auth/*`): login (GET/POST), register (GET/POST), forgot-password (GET/POST), reset-password/{token} (GET, signed), reset-password (POST, throttle:6,1), magic-link (GET/POST, throttle:5,1)

**Auth routes** (`/auth/*`): logout (ANY), verify-email (GET), verify-email/{id}/{hash} (GET, signed), email/verification-notification (POST, throttle:6,1), password (PUT)

**Socialite** (outside guest/auth groups): `auth.socialite.redirect` (GET), `auth.socialite.callback` (GET), `auth.socialite.disconnect` (DELETE, auth)

**Magic Link** (outside guest/auth groups): `magic-link.authenticate` — `/auth/magic-link/{token}` (GET) — must be accessible from email clients

**Impersonation**: `/auth/impersonate/{userId}` (POST, auth)

**API**: `/api/v1/auth/me` (GET, auth:sanctum)

## Patterns

### Socialite Dual-Flow
`SocialiteController::callback()` checks `Auth::check()` to branch:
- **Guest**: finds/creates user via `SocialiteService::handleCallback()`, logs in, redirects to intended URL
- **Authenticated**: links account via `SocialiteService::linkAccountToUser()`, redirects to `settings.profile`

### Disconnect Validation
Cannot disconnect if it's the user's only login method. `SocialiteService::disconnectProvider()` throws `SocialiteException::cannotDisconnectOnlyMethod()` when `socialAccounts->count() === 1 && !$user->password`.

Also prevents account takeover: linking a social ID already owned by another user throws `accountAlreadyLinked`.

### Rate Limiting
`LoginRequest::ensureIsNotRateLimited()` — 5 attempts per `email|ip` key. Fires `Lockout` event, throws `AuthException::throttle($seconds)`. Cleared on success.

### Impersonation
Uses `lab404/laravel-impersonate` + `filament-impersonate`. Session stores history at `impersonation.recent_history` (max 4 user IDs). `ReimpersonateController` lets admins re-impersonate from recent list (max 3 shown in UI, filters deleted users and self). Stop via `filament-impersonate.leave` route.

### Magic Link Flow
`MagicLinkController::store()` silently finds the user by email (no error on unknown email). If found: deletes existing tokens for that user, creates a new `MagicLinkToken` (token = SHA-256 hash of `Str::random(64)`, expires in 15 min), and sends `MagicLinkNotification` with the plain-token URL.

`MagicLinkController::authenticate()` hashes the incoming token, looks it up, calls `isValid()` (not expired + not used), logs in the user, marks the token used, and redirects to intended URL or dashboard.

**Token storage:** Plain token only lives in the email link. DB stores `hash('sha256', $plainToken)`. This means even if the DB is compromised, tokens cannot be forged or replayed.

### Logout Action Handler
`app.ts` registers a logout icon and action handler via `registerIcon('logout', ...)` and `registerAction('logout', ...)` from `@/lib/navigation`. The action shows a confirmation dialog and posts to `route('logout')`.

## ENV Variables

```
GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_CLIENT_REDIRECT_URI
GITHUB_CLIENT_ID, GITHUB_CLIENT_SECRET, GITHUB_CLIENT_REDIRECT_URI
```

Redirect URIs default to `/auth/socialite/{provider}/callback`. Providers configured in `config/services.php`.

## Testing

```bash
php artisan test --testsuite=Modules --filter='^Modules\\Auth\\Tests'  # PHPUnit
npx playwright test --project="@Auth*"                 # E2E
```

**E2E coverage**: login (basic, errors, security/rate-limiting, social, logout), register (basic, errors), forgot-password (basic, errors), verify-email. Page objects in `tests/e2e/pages/`, fixtures in `tests/e2e/fixtures/users.ts`.

## Gotchas

- `LoginRequest::validateCredentials()` validates without logging in — the controller handles `Auth::login()` separately
- Social users get email auto-verified (`email_verified_at = now()`) and a random password
- Socialite redirect/callback routes are outside both guest and auth middleware groups
- `RegisterRequest::passedValidation()` hashes the password before the controller sees it
- Filament UserResource enforces single role (maxItems: 1) despite multi-select UI
- Magic link authenticate route is outside both guest and auth middleware groups (link is clicked from email client)
- `MagicLinkToken::isValid()` checks both `expires_at->isFuture()` and `used_at === null`
