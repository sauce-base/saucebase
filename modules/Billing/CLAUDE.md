# Billing Module

Subscription management, checkout sessions, payment processing, and webhook handling via a payment gateway driver pattern (Stripe default).

## Key Files

| Layer       | Files                                                                                                                                                                                                                                                   |
| ----------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Controllers | `CheckoutController` (create, show, store), `SubscriptionController` (cancel, resume), `BillingPortalController` (portal redirect), `SettingsBillingController` (show), `WebhookController` (invoke)                                                    |
| Models      | `Customer`, `Subscription`, `Product`, `Price`, `Payment`, `PaymentMethod`, `Invoice`, `CheckoutSession`, `PaymentProvider`, `WebhookEvent`                                                                                                             |
| Services    | `BillingService` — main orchestrator (checkout, webhooks, cancel/resume, portal), `PaymentGatewayManager` — driver manager (extends Laravel's Manager)                                                                                                  |
| Gateway     | `StripeGateway` — implements `PaymentGatewayInterface` (create customer/session, cancel/resume, portal, webhook verify)                                                                                                                                 |
| Enums       | `SubscriptionStatus`, `PaymentStatus`, `CheckoutSessionStatus`, `InvoiceStatus`, `PaymentMethodType`, `Currency`, `BillingScheme`, `WebhookEventType`                                                                                                   |
| Events      | `SubscriptionCreated`, `SubscriptionUpdated`, `SubscriptionCancelled`, `SubscriptionResumed`, `PaymentSucceeded`, `PaymentFailed`, `InvoicePaid`, `CheckoutCompleted`                                                                                   |
| Listeners   | `SyncSubscriberRole`, `SendSubscriptionCreatedNotification`, `SendSubscriptionUpdatedNotification`, `SendSubscriptionCancelledNotification`, `SendSubscriptionResumedNotification`, `SendPaymentSucceededNotification`, `SendPaymentFailedNotification` |
| Data        | `CheckoutData`, `CheckoutResultData`, `WebhookData`, `CustomerData`, `AddressData`, `PaymentMethodData`, `PaymentMethodDetails` (Spatie Data objects)                                                                                                   |
| Commands    | `ExpireCheckoutSessionsCommand` — runs every 30 minutes, marks abandoned/expired sessions                                                                                                                                                               |
| Middleware  | `RedirectToRegister` — redirects guests on checkout pages, stores intended URL                                                                                                                                                                          |
| Filament    | `BillingPlugin`, `BillingDashboard` (date range stats), `ProductResource`, `SubscriptionResource`, `CustomerResource`                                                                                                                                   |
| Trait       | `Billable` — added to User model (`billingCustomer()` HasOne relationship)                                                                                                                                                                              |
| Pages       | `SettingsBilling`, `Checkout`                                                                                                                                                                                                                           |

## Routes

**Checkout** (no auth required):

```
POST  /billing/checkout                      → billing.checkout.create  (throttle:10,1)
GET   /billing/checkout/{checkout_session}   → billing.checkout.show    (RedirectToRegister)
POST  /billing/checkout/{checkout_session}   → billing.checkout.store   (RedirectToRegister)
```

**Auth required**:

```
GET   /billing/portal                → billing.portal
POST  /billing/subscription/cancel   → billing.subscription.cancel
POST  /billing/subscription/resume   → billing.subscription.resume
GET   /settings/billing              → settings.billing
```

**Webhook** (no CSRF):

```
POST  /billing/webhooks/{provider}   → billing.webhooks
```

## Patterns

### Checkout Flow

`CheckoutController::create()` validates the `price_id`, creates a `CheckoutSession` record (status: Pending), and redirects. `CheckoutController::store()` collects billing details (name, email, address), calls `BillingService::processCheckout()` which:

1. Calls `ensureCustomer()` — finds or creates the `Customer` record and the Stripe customer
2. Calls `StripeGateway::createCheckoutSession()` — returns a Stripe session URL
3. Updates `CheckoutSession` with `provider_session_id`
4. Redirects via `Inertia::location()` to the Stripe-hosted page

On return, `SettingsBillingController::show()` checks for a `session_id` query param and calls `BillingService::fulfillCheckoutIfNeeded()` as a fallback (redirect-based completion in case the webhook hasn't fired yet).

### Webhook Processing

`BillingService::handleWebhook()`:

1. Calls `gateway->verifyAndParseWebhook()` — signature verification, maps Stripe event type to `WebhookEventType` enum
2. Deduplicates by `provider_event_id` in `webhook_events` table — returns 200 immediately if already processed
3. Routes to private handlers via match on `WebhookEventType`:
    - `CheckoutCompleted` → creates Subscription, Payment, PaymentMethod; handles race condition with pre-created payments
    - `SubscriptionUpdated` → maps Stripe status to `SubscriptionStatus` (active/trialing → Active, past_due/unpaid → PastDue, canceled → Cancelled), syncs period dates
    - `SubscriptionDeleted` → marks Cancelled, fires `SubscriptionCancelled`
    - `PaymentSucceeded` → creates Payment, restores PastDue subscription to Active
    - `PaymentFailed` → creates Payment, marks subscription PastDue
    - `InvoicePaid` → creates/updates Invoice, syncs subscription period dates

### Subscription Lifecycle

Cancellation is always at period end by default. `SubscriptionController::cancel()` calls `BillingService::cancel()` which sets `cancelled_at` and `ends_at` on the Subscription. Resume calls `gateway->resumeSubscription()` (sets Stripe `cancel_at_period_end: false`) and clears those timestamps locally.

Status transitions driven by webhooks: Active → PastDue (failed payment) → Cancelled (subscription deleted or never recovered). A cancelled subscription can be resumed while `ends_at` is in the future.

### Role Syncing

`SyncSubscriberRole` listens to `SubscriptionCreated|SubscriptionUpdated|SubscriptionCancelled`. Assigns `Role::SUBSCRIBER` when the subscription status is Active or PastDue. Removes the role only if the user has no other active subscriptions (to handle multiple subscriptions).

### Gateway Driver Pattern

`PaymentGatewayManager` extends Laravel's `Manager`. The default driver is `billing.default_gateway` config. Adding a new gateway means implementing `PaymentGatewayInterface` and adding a `createXxxDriver()` method — `BillingService` requires no changes.

### Payment Race Conditions

Stripe can fire `PaymentSucceeded` before `CheckoutCompleted`. `onPaymentSucceeded()` creates a Payment with no `subscription_id`. When `onCheckoutCompleted()` fires later, it finds the orphaned payment by `provider_payment_id` and links it to the new Subscription.

## ENV Variables

```
STRIPE_SECRET_KEY=sk_...
STRIPE_PUBLISHABLE_KEY=pk_...
STRIPE_WEBHOOK_SECRET=whsec_...

BILLING_GATEWAY=stripe                   # stripe | paddle | lemonsqueezy
BILLING_DEFAULT_CURRENCY=EUR             # ISO 4217
BILLING_CHECKOUT_ABANDON_MINUTES=60
BILLING_CHECKOUT_EXPIRE_MINUTES=1440
BILLING_LOGGING_ENABLED=true
BILLING_LOG_CHANNEL=stack
```

## Testing

```bash
php artisan test --testsuite=Modules --filter=Billing  # PHPUnit
npx playwright test --project="@Billing*"                  # E2E
```

## Debugging Billing Issues

Before diving into code, verify the external dependencies are running:

- **Stripe CLI listener** — webhooks won't fire locally without it. Run `stripe listen --forward-to localhost/billing/webhooks/stripe` and confirm the webhook secret in `.env` (`STRIPE_WEBHOOK_SECRET`) matches the CLI output. Most "subscription not created" or "event not fired" bugs in local dev are just a missing or misconfigured listener.
- **Stripe keys** — confirm `STRIPE_SECRET_KEY` and `STRIPE_PUBLISHABLE_KEY` are set and match the environment (test vs. live). A mismatched key causes silent 401s from Stripe with no local exception.
- **Queue worker** — if listeners appear registered but notifications or role sync don't happen, check whether jobs are being queued but not processed (`php artisan queue:work`).

## Gotchas

- `CheckoutSession` uses `uuid` as the route key (not `id`) — always resolve via UUID in URLs
- `fulfillCheckoutIfNeeded()` only works with Stripe (calls `StripeGateway::retrieveCheckoutSession()` directly); other gateways need their own redirect-completion logic
- Webhook signature verification happens inside `StripeGateway::verifyAndParseWebhook()` before deduplication — a bad signature throws an HttpException (400), which `WebhookController` returns as a 400 response (Stripe retries on 5xx only)
- `Price::amount` is stored in minor currency units (cents) — always divide by 100 for display; `Currency::formatAmount()` handles this
- `SyncSubscriberRole` removes the subscriber role only when the user has no other active subscriptions — check for multiple subscriptions before assuming role removal means cancellation
- `Billable` trait must be added to the User model (same pattern as Auth's `Sociable`) — `$user->billingCustomer` will fail without it
- Products use SoftDeletes; always scope to `active()` or `displayable()` when listing plans
