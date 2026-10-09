<?php

use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Models\Profile;
use App\Models\Question;
use App\Models\ReputationEvent;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DevSeeder;
use Illuminate\Support\Facades\Hash;

test('les seeders créent environ 40 devs de 8 pays et les deux comptes de démo', function () {

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
    $count = User::count();

    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe($count);
});

test('les seeders créent environ 25 missions dans tous les statuts, et la mission Wave d\'Aïssatou est ouverte', function () {

    expect(Mission::count())->toBeGreaterThanOrEqual(25)
        ->and(Mission::distinct()->pluck('status')->map->value->sort()->values()->all())
        ->toBe(['assigned', 'cancelled', 'completed', 'open']);

    $wave = Mission::where('title', 'like', '%API de paiement Wave%')->with('client')->sole();
    expect($wave->status)->toBe(MissionStatus::Open)
        ->and($wave->client->email)->toBe(DevSeeder::DEMO_STARTUP_EMAIL)
        ->and(User::where('email', DevSeeder::DEMO_DEV_EMAIL)->value('reputation'))->toBeGreaterThan(0);
});

test('les seeders créent environ 40 questions locales, sans historique fictif, avec une réputation variée et cohérente', function () {

    expect(Question::count())->toBeGreaterThanOrEqual(35)
        ->and(Question::whereNotNull('accepted_answer_id')->count())->toBeGreaterThan(20)
        ->and(Question::where('answers_count', 0)->count())->toBeGreaterThan(0)
        ->and(ReputationEvent::where('reason', 'seed_history')->count())->toBe(0)
        ->and(Question::where('title', 'like', '%webhook Wave%')->exists())->toBeTrue()
        ->and(User::max('reputation'))->toBeGreaterThan(1200)
        ->and(User::where('reputation', 0)->count())->toBeGreaterThan(3);
});
