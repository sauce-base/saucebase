<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Enums\SubscriptionStatus;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\PaymentMethod;
use Modules\Billing\Models\Price;
use Modules\Billing\Models\Subscription;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Billing\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'price_id' => Price::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'provider_subscription_id' => 'sub_'.fake()->regexify('[A-Za-z0-9]{24}'),
            'status' => SubscriptionStatus::Active,
            'trial_starts_at' => null,
            'trial_ends_at' => null,
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'cancelled_at' => null,
            'ends_at' => null,
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the subscription is on trial.
     */
    public function onTrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'trial_starts_at' => now(),
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    /**
     * Indicate that the subscription is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);
    }

    /**
     * Indicate that the subscription is past due.
     */
    public function pastDue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::PastDue,
        ]);
    }

    /**
     * Indicate that the subscription has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now()->subMonth(),
            'ends_at' => now()->subDay(),
            'current_period_ends_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the subscription is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Pending,
        ]);
    }
}
