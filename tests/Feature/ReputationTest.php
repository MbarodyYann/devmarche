<?php

use App\Models\Mission;
use App\Models\User;
use App\Services\ReputationService;
use Illuminate\Support\Facades\DB;

test('ReputationService trace l\'événement et met à jour le total', function () {
    $user = User::factory()->withProfile()->create();
    $mission = Mission::factory()->create();

    $event = app(ReputationService::class)->award($user, 20, 'mission_completed', $mission);

    expect($user->fresh()->reputation)->toBe(20)
        ->and($event->source)->toBeInstanceOf(Mission::class)
        ->and($event->source->is($mission))->toBeTrue();
});

test('reputation:recalculate remet chaque total en accord avec les événements', function () {
    $user = User::factory()->withProfile()->create();
    app(ReputationService::class)->award($user, 30, 'seed_history');
    app(ReputationService::class)->award($user, -5, 'answer_downvoted');

    DB::table('users')->where('id', $user->id)->update(['reputation' => 999]);

    $this->artisan('reputation:recalculate')->expectsOutputToContain('1 réputation(s) corrigée(s).')->assertSuccessful();

    expect($user->fresh()->reputation)->toBe(25);

    $this->artisan('reputation:recalculate')->expectsOutputToContain('cohérentes')->assertSuccessful();
});
