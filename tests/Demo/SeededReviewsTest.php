<?php

use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

test('chaque mission terminée a un avis crédible du client, daté après la fin', function () {
    $completed = Mission::where('status', MissionStatus::Completed)->count();

    expect(Review::count())->toBe($completed)
        ->and(Review::pluck('comment')->unique()->count())->toBeGreaterThanOrEqual(10)
        ->and(Review::whereColumn('reviewer_id', '!=', DB::raw('(select client_id from missions where missions.id = reviews.mission_id)'))->count())->toBe(0)
        ->and(DB::table('reviews')->join('missions', 'missions.id', '=', 'reviews.mission_id')->whereColumn('reviews.created_at', '<', 'missions.completed_at')->count())->toBe(0)
        ->and(Review::avg('rating'))->toBeBetween(3.8, 4.8);
});

test('la démo montre au moins un « Client fidèle »', function () {
    $loyal = Mission::query()->whereNotNull('freelancer_id')
        ->select('client_id', 'freelancer_id')->groupBy('client_id', 'freelancer_id')->havingRaw('count(*) >= 2')->count();

    expect($loyal)->toBeGreaterThanOrEqual(1);
});

test('« Deuxième mission avec lui » seulement pour un client qui a déjà confié une mission au dev', function () {
    foreach (Review::where('comment', 'like', 'Deuxième mission%')->with('mission')->get() as $review) {
        expect(Mission::where('client_id', $review->reviewer_id)->where('freelancer_id', $review->reviewee_id)->count())->toBeGreaterThanOrEqual(2);
    }
});
