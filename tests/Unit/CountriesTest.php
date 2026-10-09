<?php

use App\Support\Countries;

test('les pays d\'Afrique francophone viennent en premier', function () {
    $codes = Countries::codes();

    expect(array_slice($codes, 0, count(config('countries.francophone'))))
        ->toBe(array_keys(config('countries.francophone')))
        ->and($codes)->toContain('SN', 'CI', 'CM', 'BJ', 'TG', 'BF', 'ML', 'GN')
        ->and(Countries::name('CI'))->toBe('Côte d\'Ivoire');
});

test('le drapeau est construit à partir du code ISO', function () {
    expect(Countries::flag('SN'))->toBe('🇸🇳')
        ->and(Countries::flag(null))->toBe('');
});
