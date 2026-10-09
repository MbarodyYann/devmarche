<?php

use App\Enums\ApplicationStatus;
use App\Enums\MissionStatus;

test('les statuts de mission ont des valeurs ASCII, des libellés français et des tons de puce', function () {
    expect(MissionStatus::options())->toBe([
        'open' => 'Ouverte', 'assigned' => 'En cours', 'completed' => 'Terminée', 'cancelled' => 'Annulée',
    ])
        ->and(array_map(fn ($s) => $s->tone(), MissionStatus::cases()))->toBe(['info', 'warm', 'done', 'muted']);
});

test('les statuts de candidature ont des libellés français et des tons de puce', function () {
    expect(array_map(fn ($s) => [$s->value, $s->label(), $s->tone()], ApplicationStatus::cases()))->toBe([
        ['pending', 'En attente', 'warm'],
        ['accepted', 'Retenue', 'done'],
        ['rejected', 'Non retenue', 'muted'],
    ]);
});
