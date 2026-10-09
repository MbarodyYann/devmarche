<?php

namespace Database\Factories;

use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mission>
 */
class MissionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => User::factory()->withProfile(),
            'title' => 'Mission '.fake()->words(4, true),
            'description' => fake()->paragraph(4),
            'budget_fcfa' => fake()->numberBetween(10, 500) * 1000,
            'country' => fake()->randomElement(['SN', 'CI', 'CM', null]),
            'deadline' => null,
        ];
    }

    public function remote(): static
    {
        return $this->state(['country' => null]);
    }

    public function assignedTo(User $freelancer): static
    {
        return $this->state(fn () => [])->afterCreating(function (Mission $mission) use ($freelancer) {
            $mission->forceFill([
                'status' => MissionStatus::Assigned,
                'freelancer_id' => $freelancer->id,
                'assigned_at' => now(),
            ])->save();
        });
    }

    public function withStatus(MissionStatus $status): static
    {
        return $this->afterCreating(fn (Mission $mission) => $mission->forceFill(['status' => $status])->save());
    }
}
