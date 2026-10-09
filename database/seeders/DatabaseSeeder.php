<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Données de démonstration réalistes (aucun « Lorem ipsum »).
     */
    public function run(): void
    {
        $this->call([
            DevSeeder::class,
            MissionSeeder::class,
            QuestionSeeder::class,
            ReviewSeeder::class,
            ModerationSeeder::class,
            ActivitySeeder::class,
        ]);
    }
}
