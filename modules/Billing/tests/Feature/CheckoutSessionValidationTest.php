<?php

namespace Modules\Billing\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Contracts\PaymentGatewayInterface;
use Modules\Billing\Data\CheckoutResultData;
use Modules\Billing\Data\CustomerData;
use Modules\Billing\Enums\CheckoutSessionStatus;
use Modules\Billing\Models\CheckoutSession;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Price;
use Modules\Billing\Services\PaymentGatewayManager;
use Tests\TestCase;

class CheckoutSessionValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $gateway = $this->createMock(PaymentGatewayInterface::class);
        $gateway->method('createCustomer')->willReturnCallback(
            fn (CustomerData $data) => Customer::create([
                'user_id' => $data->user->id,
                'provider_customer_id' => 'cus_test_123',
                'email' => $data->email,
                'name' => $data->name,
                'phone' => $data->phone,
                'address' => $data->address?->toArray(),
            ]),
        );
        $gateway->method('createCheckoutSession')->willReturn(
            new CheckoutResultData(sessionId: 'cs_test_123', url: 'https://stripe.com/checkout', provider: 'stripe'),
        );

        $manager = $this->createMock(PaymentGatewayManager::class);
        $manager->method('driver')->willReturn($gateway);
        $this->app->instance(PaymentGatewayManager::class, $manager);
    }

    public function test_show_rejects_expired_checkout_session(): void
    {
        $session = CheckoutSession::factory()->create([
            'status' => CheckoutSessionStatus::Pending,
            'expires_at' => now()->subHour(),
        ]);

        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('billing.checkout', $session));

        $response->assertStatus(410);
    }

    public function test_show_rejects_completed_checkout_session(): void
    {
        $session = CheckoutSession::factory()->completed()->create();

        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('billing.checkout', $session));

        $response->assertStatus(410);
    }

    public function test_store_rejects_expired_checkout_session(): void
    {
        $session = CheckoutSession::factory()->create([
            'status' => CheckoutSessionStatus::Pending,
            'expires_at' => now()->subHour(),
        ]);

        $user = $this->createUser();

        $response = $this->actingAs($user)->post(route('billing.checkout.store', $session), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(410);
    }

    public function test_store_rejects_completed_checkout_session(): void
    {
        $session = CheckoutSession::factory()->completed()->create();

        $user = $this->createUser();

        $response = $this->actingAs($user)->post(route('billing.checkout.store', $session), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(410);
    }

    public function test_create_rejects_inactive_price(): void
    {
        $price = Price::factory()->inactive()->create();

        $response = $this->post(route('billing.checkout.create'), [
            'price_id' => $price->id,
        ]);

        $response->assertSessionHasErrors('price_id');
    }

    public function test_store_rejects_checkout_session_owned_by_another_user(): void
    {
        $owner = $this->createUser();
        $attacker = $this->createUser();

        $customer = \Modules\Billing\Models\Customer::create([
            'user_id' => $owner->id,
            'provider_customer_id' => 'cus_owner',
            'name' => $owner->name,
            'email' => $owner->email,
        ]);

        $session = CheckoutSession::factory()->create([
            'customer_id' => $customer->id,
            'status' => CheckoutSessionStatus::Pending,
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->actingAs($attacker)->post(route('billing.checkout.store', $session), [
            'name' => 'Attacker',
            'email' => 'attacker@example.com',
        ]);

        $response->assertStatus(403);
    }

    public function test_billing_portal_redirects_when_no_customer(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('billing.portal'));

        $response->assertRedirect(route('settings.billing'));
        $response->assertSessionHas('toast');
    }
}
