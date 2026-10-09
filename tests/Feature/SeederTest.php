<?php

use App\Models\Profile;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DevSeeder;
use Illuminate\Support\Facades\Hash;

test('les seeders créent environ 40 devs de 8 pays et les deux comptes de démo', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBeGreaterThanOrEqual(40)
        ->and(Profile::distinct()->count('country'))->toBeGreaterThanOrEqual(8);

    $aissatou = User::with('profile')->where('email', DevSeeder::DEMO_STARTUP_EMAIL)->sole();
    $moussa = User::with(['profile', 'skills'])->where('email', DevSeeder::DEMO_DEV_EMAIL)->sole();

    expect($aissatou->profile->city)->toBe('Dakar')
        ->and($moussa->profile->city)->toBe('Thiès')
        ->and(Hash::check('password', $moussa->password))->toBeTrue()
        ->and($moussa->skills->pluck('name'))->toContain('Laravel', 'Wave API', 'Orange Money');
});

test('les seeders peuvent être relancés sans doublon', function () {
    $this->seed(DatabaseSeeder::class);
    $count = User::count();

    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe($count);
});
