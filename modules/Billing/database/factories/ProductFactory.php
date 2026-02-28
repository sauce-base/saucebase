<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Billing\Models\Product;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Billing\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true).' Plan';

        return [
            'sku' => strtoupper(Str::slug(fake()->unique()->word())),
            'slug' => Str::slug($name),
            'name' => $name,
            'description' => fake()->sentence(12),
            'display_order' => fake()->numberBetween(0, 10),
            'is_visible' => true,
            'is_highlighted' => false,
            'is_active' => true,
            'features' => $this->generateFeatures(),
            'metadata' => null,
        ];
    }

    /**
     * Generate random features array.
     */
    protected function generateFeatures(): array
    {
        return [
            'storage_gb' => fake()->randomElement([5, 10, 50, 100, 500, 1000]),
            'max_users' => fake()->randomElement([1, 5, 10, 25, 50, 100]),
            'support' => fake()->randomElement(['community', 'email', 'priority', '24/7']),
            'api_calls_per_month' => fake()->randomElement([1000, 10000, 100000, 'unlimited']),
        ];
    }

    /**
     * Indicate that the product is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product is visible.
     */
    public function visible(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => true,
        ]);
    }

    /**
     * Indicate that the product is hidden.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => false,
        ]);
    }

    /**
     * Indicate that the product is highlighted.
     */
    public function highlighted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_highlighted' => true,
        ]);
    }

    /**
     * Set custom features for the product.
     */
    public function withFeatures(array $features): static
    {
        return $this->state(fn (array $attributes) => [
            'features' => $features,
        ]);
    }

    /**
     * Set custom metadata for the product.
     */
    public function withMetadata(array $metadata): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => $metadata,
        ]);
    }

    /**
     * Create a complete starter plan.
     */
    public function starter(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Starter Plan',
            'sku' => 'STARTER',
            'slug' => 'starter-plan',
            'display_order' => 1,
            'is_active' => true,
            'is_visible' => true,
            'features' => [
                'storage_gb' => 10,
                'max_users' => 3,
                'support' => 'email',
                'api_calls_per_month' => 10000,
            ],
        ]);
    }

    /**
     * Create a complete pro plan.
     */
    public function pro(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Pro Plan',
            'sku' => 'PRO',
            'slug' => 'pro-plan',
            'display_order' => 2,
            'is_active' => true,
            'is_visible' => true,
            'is_highlighted' => true,
            'features' => [
                'storage_gb' => 100,
                'max_users' => 10,
                'support' => 'priority',
                'api_calls_per_month' => 100000,
            ],
        ]);
    }

    /**
     * Create a complete enterprise plan.
     */
    public function enterprise(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Enterprise Plan',
            'sku' => 'ENTERPRISE',
            'slug' => 'enterprise-plan',
            'display_order' => 3,
            'is_active' => true,
            'is_visible' => true,
            'features' => [
                'storage_gb' => 1000,
                'max_users' => 100,
                'support' => '24/7',
                'api_calls_per_month' => 'unlimited',
            ],
        ]);
    }
}
