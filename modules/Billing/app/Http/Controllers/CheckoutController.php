<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Billing\Enums\CheckoutSessionStatus;
use Modules\Billing\Models\CheckoutSession;
use Modules\Billing\Services\BillingService;

class CheckoutController
{
    public function __construct(
        private BillingService $billingService,
    ) {}

    public function create(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'price_id' => ['required', 'exists:prices,id,is_active,1'],
        ]);

        $session = CheckoutSession::create([
            'price_id' => $validated['price_id'],
            'status' => CheckoutSessionStatus::Pending,
            'expires_at' => now()->addMinutes(config('billing.checkout.expire_after_minutes', 1440)),
        ]);

        return redirect()->route('billing.checkout', $session);
    }

    public function show(CheckoutSession $checkoutSession): Response
    {
        abort_if($checkoutSession->status !== CheckoutSessionStatus::Pending, 410);
        abort_if($checkoutSession->expires_at?->isPast(), 410);

        $checkoutSession->load('price.product');

        return Inertia::render('Billing::Checkout', [
            'session' => $checkoutSession,
        ]);
    }

    public function store(Request $request, CheckoutSession $checkoutSession): \Symfony\Component\HttpFoundation\Response
    {
        abort_if($checkoutSession->status !== CheckoutSessionStatus::Pending, 410);
        abort_if($checkoutSession->expires_at?->isPast(), 410);

        if ($checkoutSession->customer_id && $checkoutSession->customer?->user_id !== $request->user()?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.city' => ['nullable', 'string', 'max:255'],
            'address.state' => ['nullable', 'string', 'max:255'],
            'address.postal_code' => ['nullable', 'string', 'max:20'],
            'address.country' => ['nullable', 'string', 'max:2'],
        ]);

        $result = $this->billingService->processCheckout(
            session: $checkoutSession,
            user: $request->user(),
            successUrl: route('settings.billing').'?session_id={CHECKOUT_SESSION_ID}',
            cancelUrl: route('billing.checkout', $checkoutSession),
            billingDetails: $validated,
        );

        return Inertia::location($result->url);
    }
}
