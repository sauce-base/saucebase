<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Billing\Enums\CheckoutSessionStatus;
use Modules\Billing\Models\CheckoutSession;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Price;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Billing\Models\CheckoutSession>
 */
class CheckoutSessionFactory extends Factory
{
    protected $model = CheckoutSession::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'customer_id' => Customer::factory(),
            'price_id' => Price::factory(),
            'provider_session_id' => 'cs_'.fake()->regexify('[A-Za-z0-9]{24}'),
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
            'status' => CheckoutSessionStatus::Pending,
            'metadata' => null,
            'expires_at' => now()->addHours(24),
        ];
    }

    /**
     * Indicate that the session is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CheckoutSessionStatus::Completed,
        ]);
    }

    /**
     * Indicate that the session is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CheckoutSessionStatus::Expired,
            'expires_at' => now()->subHour(),
        ]);
    }

    /**
     * Indicate that the session is abandoned.
     */
    public function abandoned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CheckoutSessionStatus::Abandoned,
        ]);
    }
}
