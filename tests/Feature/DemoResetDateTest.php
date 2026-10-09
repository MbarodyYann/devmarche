<?php

use App\Models\Mission;
use App\Models\Question;
use App\Models\Review;
use App\Models\User;
use App\Support\Demo;
use App\Support\Money;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DevSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/*
| La démo est réinitialisée chaque nuit : sa chronologie suit la date de la réinitialisation.
| Simulation d'un demo:reset le 1er nov. 2026 (jour de la notation).
*/

test('après une réinitialisation le 1er nov., la démo est datée des 30 derniers jours', function () {
    Carbon::setTestNow('2026-11-01 08:00:00');
    $this->seed(DatabaseSeeder::class);

    $now = Carbon::now();
    $windowStart = $now->copy()->subDays(30)->subMinutes(20);

    // La mission et la question les plus récentes ont moins de 24 h.
    expect(Mission::max('created_at'))->toBeGreaterThan($now->copy()->subDay()->toDateTimeString())
        ->and(Question::max('created_at'))->toBeGreaterThan($now->copy()->subDays(2)->toDateTimeString());

    // Rien avant le lancement, rien avant la fenêtre de 30 jours, rien dans le futur.
    foreach (['users', 'missions', 'applications', 'questions', 'answers', 'votes', 'reputation_events'] as $table) {
        expect(DB::table($table)->where('created_at', '<', config('devmarche.launched_at'))->count())->toBe(0, "$table avant le lancement")
            ->and(DB::table($table)->where('created_at', '<', $windowStart)->count())->toBe(0, "$table avant la fenêtre")
            ->and(DB::table($table)->where('created_at', '>', $now)->count())->toBe(0, "$table dans le futur");
    }

    // « Actif aujourd'hui » est vrai ce jour-là pour les deux comptes de démo, et l'indicateur est varié.
    foreach ([Demo::DEV_EMAIL, Demo::STARTUP_EMAIL] as $email) {
        expect(User::where('email', $email)->sole()->activityLabel())->toBe('Actif aujourd\'hui');
    }
    $today = User::all()->filter(fn (User $u) => $u->last_seen_at->isSameDay($now))->count();
    expect($today / User::count())->toBeLessThanOrEqual(0.45)
        ->and(User::all()->filter(fn (User $u) => $u->last_seen_at->lt($now->copy()->subDays(2)->startOfDay()))->count() / User::count())->toBeGreaterThanOrEqual(0.25);

    // Les nouveaux venus sont « Nouveau », Moussa ne l'est pas.
    expect(User::where('email', Demo::DEV_EMAIL)->sole()->isNewMember())->toBeFalse();
    foreach (DevSeeder::NEWCOMERS as $name) {
        $user = User::where('name', $name)->sole();
        expect($user->created_at->gt($now->copy()->subDays(4)))->toBeTrue("$name inscrit récemment");
        if ($user->reputation < 100) {
            expect($user->isNewMember())->toBeTrue("$name devrait être Nouveau");
        }
    }

    // Avis : durées réalistes (le délai proposé, à un ou deux jours près) et variées pour chaque dev.
    $reviews = Review::with('mission')->get();
    $durations = $reviews->map(fn ($r) => (int) $r->mission->assigned_at->diffInDays($r->mission->completed_at));
    expect($durations->min())->toBeGreaterThanOrEqual(4)
        ->and($durations->max())->toBeLessThanOrEqual(31);

    foreach ($reviews->groupBy('reviewee_id') as $devReviews) {
        $perDev = $devReviews->map(fn ($r) => [
            Money::budgetRange($r->mission->budget_fcfa),
            (int) $r->mission->assigned_at->diffInDays($r->mission->completed_at),
        ]);
        expect($perDev->pluck(0)->countBy()->max())->toBeLessThanOrEqual(2)
            ->and($perDev->pluck(1)->countBy()->max())->toBeLessThanOrEqual(2);
    }
})->after(fn () => Carbon::setTestNow());
