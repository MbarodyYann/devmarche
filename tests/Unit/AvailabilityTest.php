<?php

use App\Enums\Availability;

test('les disponibilités ont des valeurs ASCII et des libellés en français', function () {
    expect(Availability::options())->toBe([
        'available' => 'Disponible',
        'busy' => 'Occupé',
        'employed' => 'En poste',
    ]);
});
