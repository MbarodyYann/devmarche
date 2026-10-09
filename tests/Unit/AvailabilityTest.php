<?php

use App\Enums\Availability;

test('les disponibilités ont des valeurs ASCII et des libellés en français', function () {
    expect(Availability::options())->toBe([
        'available' => 'Disponible',
        'busy' => 'Occupé',
        'employed' => 'En poste',
    ]);
});

test('chaque disponibilité a un tampon et une encre', function () {
    expect(Availability::Available->stamp())->toBe('Dispo')
        ->and(Availability::Busy->stamp())->toBe('Occupé')
        ->and(Availability::Employed->stamp())->toBe('En poste')
        ->and(Availability::Available->tone())->toBe('success')
        ->and(Availability::Busy->tone())->toBe('muted')
        ->and(Availability::Employed->tone())->toBe('secondary');
});
