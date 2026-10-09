<?php

use App\Support\French;

test('« de » s\'élide devant une voyelle, même accentuée', function (string $name, string $expected) {
    expect(French::de($name))->toBe($expected);
})->with([
    ['Aïssatou Diallo', "d'Aïssatou Diallo"],
    ['Edem Afenyo', "d'Edem Afenyo"],
    ['Oumou Dembélé', "d'Oumou Dembélé"],
    ['Ibrahima Diop', "d'Ibrahima Diop"],
    ['Abdoul Kaboré', "d'Abdoul Kaboré"],
    ['Élodie', "d'Élodie"],
    ['Orange Money', "d'Orange Money"],
    ['Moussa Ndiaye', 'de Moussa Ndiaye'],
    ['Yawo Amegbor', 'de Yawo Amegbor'],
    ['Wave', 'de Wave'],
    ['Hawa Soumah', 'de Hawa Soumah'],
    ['Hélène', "d'Hélène"],
]);
