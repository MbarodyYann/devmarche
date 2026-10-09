<?php

namespace Database\Factories;

use App\Models\Mission;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        $rating = fake()->numberBetween(3, 5);

        return [
            'mission_id' => Mission::factory(),
            'reviewer_id' => User::factory(),
            'reviewee_id' => User::factory(),
            'rating' => $rating,
            'communication' => $rating,
            'quality' => $rating,
            'timeliness' => max(1, $rating - 1),
            'comment' => fake()->sentence(12),
        ];
    }
}
