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

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    private CheckoutSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $price = Price::factory()->create();
        $this->session = CheckoutSession::create([
            'price_id' => $price->id,
            'status' => CheckoutSessionStatus::Pending,
            'expires_at' => now()->addHours(24),
        ]);

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

    public function test_checkout_requires_authentication(): void
    {
        $response = $this->post(route('billing.checkout.store', $this->session), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertRedirect(route('register'));
    }

    public function test_checkout_validates_required_fields(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post(route('billing.checkout.store', $this->session), []);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_authenticated_checkout_uses_existing_user(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post(route('billing.checkout.store', $this->session), [
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $response->assertRedirect('https://stripe.com/checkout');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('customers', [
            'user_id' => $user->id,
        ]);
    }

    public function test_checkout_stores_billing_details_on_customer(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post(route('billing.checkout.store', $this->session), [
            'name' => 'Billing Name',
            'email' => 'billing@example.com',
            'phone' => '+1234567890',
            'address' => [
                'street' => '123 Main St',
                'city' => 'Springfield',
                'state' => 'IL',
                'postal_code' => '62701',
                'country' => 'US',
            ],
        ]);

        $response->assertRedirect('https://stripe.com/checkout');

        $this->assertDatabaseHas('customers', [
            'user_id' => $user->id,
            'name' => 'Billing Name',
            'email' => 'billing@example.com',
            'phone' => '+1234567890',
        ]);

        $customer = \Modules\Billing\Models\Customer::where('user_id', $user->id)->first();
        $this->assertEquals([
            'country' => 'US',
            'line1' => '123 Main St',
            'line2' => null,
            'city' => 'Springfield',
            'state' => 'IL',
            'postalCode' => '62701',
        ], $customer->address);
    }
}
