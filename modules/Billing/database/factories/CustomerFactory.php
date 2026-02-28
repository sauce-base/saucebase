<?php

namespace Modules\Billing\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Models\Customer;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Billing\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider_customer_id' => 'cus_'.fake()->regexify('[A-Za-z0-9]{14}'),
            'email' => fake()->safeEmail(),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'address' => null,
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the customer has a full address.
     */
    public function withAddress(): static
    {
        return $this->state(fn (array $attributes) => [
            'address' => [
                'line1' => fake()->streetAddress(),
                'line2' => fake()->optional()->streetSuffix(),
                'city' => fake()->city(),
                'state' => fake()->randomElement(['CA', 'NY', 'TX', 'FL', 'WA']),
                'postal_code' => fake()->postcode(),
                'country' => fake()->countryCode(),
            ],
        ]);
    }

    /**
     * Indicate that the customer has no provider ID.
     */
    public function withoutProvider(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider_customer_id' => null,
        ]);
    }
}
