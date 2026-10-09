<?php

use App\Enums\Availability;
use App\Models\User;

test('la page 404 est en français et montre les yeux du car rapide', function () {
    $this->get('/cette-page-n-existe-pas')
        ->assertNotFound()
        ->assertSee('On a regardé partout…')
        ->assertSee('clip-path', false);
});

test('l\'annuaire n\'affiche qu\'une seule recherche', function () {
    $this->get('/devs')
        ->assertOk()
        ->assertDontSee('id="recherche-globale"', false)
        ->assertSee('id="filtre-q"', false);
});

test('les autres pages gardent la recherche de l\'en-tête', function () {
    $dev = User::factory()->withProfile()->create();

    $this->get(route('devs.show', $dev))->assertSee('id="recherche-globale"', false);
});

test('la disponibilité s\'affiche en tampon, sans pastille', function () {
    $dev = User::factory()->withProfile(['availability' => Availability::Available])->create();

    $this->get(route('devs.show', $dev))
        ->assertSee('tampon', false)
        ->assertSee('Dispo')
        ->assertSee('Pas encore de cauris');

    $dev->forceFill(['reputation' => 1240])->save();

    $this->get(route('devs.show', $dev))->assertSee("1\u{202F}240")->assertSee('Cauris');
});

test('l\'avatar par défaut est un carré à motif pagne déterministe', function () {
    $dev = User::factory()->withProfile()->create();
    $pattern = ['losanges', 'chevrons', 'points', 'zigzags'][crc32('pagne-'.$dev->id) % 4];

    $this->get(route('devs.show', $dev))
        ->assertSee("/images/pagne-{$pattern}.svg", false)
        ->assertDontSee('rounded-full', false);
});
