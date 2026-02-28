<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Enums\BillingScheme;
use Modules\Billing\Enums\Currency;
use Modules\Billing\Models\Price;
use Modules\Billing\Models\Product;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Billing\Models\Price>
 */
class PriceFactory extends Factory
{
    protected $model = Price::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'payment_provider_id' => null,
            'provider_price_id' => 'price_'.fake()->regexify('[A-Za-z0-9]{24}'),
            'currency' => Currency::default(),
            'amount' => fake()->randomElement([999, 1999, 4999]),
            'billing_scheme' => BillingScheme::FlatRate,
            'interval' => 'month',
            'interval_count' => 1,
            'metadata' => null,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the price is yearly.
     */
    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'interval' => 'year',
            'interval_count' => 1,
        ]);
    }

    /**
     * Indicate that the price is for a one-time payment.
     */
    public function oneTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'interval' => null,
            'interval_count' => null,
        ]);
    }

    /**
     * Indicate that the price is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
