<?php

use App\Enums\Availability;

test('les disponibilités ont des valeurs ASCII et des libellés en français', function () {
    expect(Availability::options())->toBe([
        'available' => 'Disponible',
        'busy' => 'Occupé',
        'employed' => 'En poste',
    ]);
});

test('chaque disponibilité a un ton de puce issu des deux couleurs de la marque', function () {
    expect(Availability::Available->tone())->toBe('info')
        ->and(Availability::Busy->tone())->toBe('warm')
        ->and(Availability::Employed->tone())->toBe('muted');
});
