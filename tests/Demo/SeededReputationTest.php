<?php

use App\Models\User;

test('après les seeders, la réputation de chacun est la somme de ses événements', function () {
    $incoherent = User::query()
        ->whereRaw('reputation <> (select coalesce(sum(points), 0) from reputation_events where reputation_events.user_id = users.id)')
        ->count();

    expect($incoherent)->toBe(0)
        ->and(User::max('reputation'))->toBeBetween(1200, 1800)
        ->and(User::where('reputation', 0)->count())->toBeGreaterThan(0);
});
