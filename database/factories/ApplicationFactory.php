<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mission_id' => Mission::factory(),
            'user_id' => User::factory()->withProfile(),
            'message' => fake()->paragraph(2),
            'proposed_fcfa' => fake()->numberBetween(10, 300) * 1000,
        ];
    }
}
