<?php

namespace Database\Factories;

use App\Enums\Availability;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'headline' => fake()->randomElement(['Dev Laravel', 'Dev Flutter', 'Dev React & Node', 'Dev Django']),
            'bio' => fake()->sentence(12),
            'country' => fake()->randomElement(['SN', 'CI', 'CM', 'BJ']),
            'city' => fake()->randomElement(['Dakar', 'Abidjan', 'Douala', 'Cotonou']),
            'availability' => fake()->randomElement(Availability::cases()),
        ];
    }
}
