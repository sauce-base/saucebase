<?php

namespace Modules\Billing\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Modules\Billing\Enums\InvoiceStatus;
use Modules\Billing\Enums\SubscriptionStatus;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\PaymentMethod;
use Modules\Billing\Models\Subscription;
use Tests\TestCase;

class BillingSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_billing_page_requires_auth(): void
    {
        $response = $this->get(route('settings.billing'));

        $response->assertRedirect(route('login'));
    }

    public function test_billing_page_shows_no_subscription_state(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('settings.billing'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Billing::SettingsBilling', false)
                ->where('subscription', null)
                ->where('paymentMethod', null)
                ->has('invoices', 0)
        );
    }

    public function test_billing_page_shows_active_subscription(): void
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $subscription = Subscription::factory()->create([
            'customer_id' => $customer->id,
            'status' => SubscriptionStatus::Active,
        ]);

        $response = $this->actingAs($user)->get(route('settings.billing'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Billing::SettingsBilling', false)
                ->where('subscription.id', $subscription->id)
                ->where('subscription.status', 'active')
                ->has('subscription.plan_name')
        );
    }

    public function test_billing_page_shows_invoices(): void
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        Invoice::factory()->count(3)->create([
            'customer_id' => $customer->id,
            'status' => InvoiceStatus::Paid,
        ]);

        $response = $this->actingAs($user)->get(route('settings.billing'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('invoices', 3)
        );
    }

    public function test_billing_page_shows_payment_method(): void
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        PaymentMethod::factory()->visa()->default()->create([
            'customer_id' => $customer->id,
            'details' => ['brand' => 'visa', 'last4' => '4242', 'expMonth' => 12, 'expYear' => 2030],
        ]);

        $response = $this->actingAs($user)->get(route('settings.billing'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->where('paymentMethod.type', 'card')
                ->where('paymentMethod.category', 'card')
                ->where('paymentMethod.details.brand', 'visa')
                ->where('paymentMethod.details.last4', '4242')
        );
    }
}
