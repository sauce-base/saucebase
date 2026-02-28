<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Enums\Currency;
use Modules\Billing\Enums\PaymentStatus;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Payment;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Billing\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'subscription_id' => null,
            'payment_method_id' => null,
            'price_id' => null,
            'provider_payment_id' => 'pi_'.fake()->regexify('[A-Za-z0-9]{24}'),
            'currency' => Currency::default(),
            'amount' => fake()->randomElement([999, 1999, 4999]),
            'amount_refunded' => 0,
            'status' => PaymentStatus::Succeeded,
            'failure_code' => null,
            'failure_message' => null,
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Pending,
        ]);
    }

    /**
     * Indicate that the payment has failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Failed,
            'failure_code' => 'card_declined',
            'failure_message' => 'Your card was declined.',
        ]);
    }

    /**
     * Indicate that the payment has been refunded.
     */
    public function refunded(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? 999;

            return [
                'status' => PaymentStatus::Refunded,
                'amount_refunded' => $amount,
            ];
        });
    }

    /**
     * Indicate that the payment has been partially refunded.
     */
    public function partiallyRefunded(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? 999;

            return [
                'status' => PaymentStatus::Succeeded,
                'amount_refunded' => (int) ($amount / 2),
            ];
        });
    }
}
