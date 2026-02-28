<?php

namespace Modules\Auth\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\SocialAccount;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Auth\Models\SocialAccount>
 */
class SocialAccountFactory extends Factory
{
    protected $model = SocialAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => $this->faker->randomElement(['github', 'google', 'facebook']),
            'provider_id' => $this->faker->unique()->randomNumber(8),
            'provider_token' => $this->faker->sha256(),
            'provider_refresh_token' => $this->faker->sha256(),
            'provider_avatar_url' => $this->faker->imageUrl(200, 200),
            'last_login_at' => now(),
        ];
    }
}
