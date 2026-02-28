<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Billing\Models\PaymentProvider;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Billing\Models\PaymentProvider>
 */
class PaymentProviderFactory extends Factory
{
    protected $model = PaymentProvider::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'config' => null,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the provider is Stripe.
     */
    public function stripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Stripe',
            'slug' => 'stripe',
        ]);
    }

    /**
     * Indicate that the provider is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
