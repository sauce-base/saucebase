<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Enums\PaymentMethodType;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\PaymentMethod;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Billing\Models\PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'provider_payment_method_id' => 'pm_'.fake()->regexify('[A-Za-z0-9]{24}'),
            'type' => PaymentMethodType::Card,
            'details' => [
                'brand' => fake()->randomElement(['visa', 'mastercard', 'amex']),
                'last4' => fake()->numerify('####'),
                'expMonth' => fake()->numberBetween(1, 12),
                'expYear' => now()->addYears(3)->year,
            ],
            'metadata' => null,
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'details' => array_merge($attributes['details'] ?? [], [
                'expMonth' => 1,
                'expYear' => now()->subYear()->year,
            ]),
        ]);
    }

    public function visa(): static
    {
        return $this->state(fn (array $attributes) => [
            'details' => array_merge($attributes['details'] ?? [], [
                'brand' => 'visa',
            ]),
        ]);
    }

    public function mastercard(): static
    {
        return $this->state(fn (array $attributes) => [
            'details' => array_merge($attributes['details'] ?? [], [
                'brand' => 'mastercard',
            ]),
        ]);
    }

    public function paypal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PaymentMethodType::PayPal,
            'details' => [
                'email' => fake()->email(),
            ],
        ]);
    }

    public function sepa(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PaymentMethodType::SepaDebit,
            'details' => [
                'last4' => fake()->numerify('####'),
                'country' => 'DE',
            ],
        ]);
    }
}
