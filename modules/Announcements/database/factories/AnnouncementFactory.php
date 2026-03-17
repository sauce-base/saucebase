<?php

namespace Modules\Announcements\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Announcements\Models\Announcement;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'text' => $this->faker->sentence(),
            'is_active' => false,
            'is_dismissable' => false,
            'show_on_frontend' => true,
            'show_on_dashboard' => true,
            'starts_at' => null,
            'ends_at' => null,
            'created_by' => User::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }

    public function dismissable(): static
    {
        return $this->state(['is_dismissable' => true]);
    }
}
