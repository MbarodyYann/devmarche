<?php

use App\Enums\Availability;
use App\Models\User;

test('la page 404 est en français', function () {
    $this->get('/cette-page-n-existe-pas')
        ->assertNotFound()
        ->assertSee('Erreur 404')
        ->assertSee('Cette page s\'est perdue en route.', false);
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

test('les polices auto-hébergées sont préchargées', function () {
    $this->get('/devs')
        ->assertSee('/fonts/newsreader-latin-wght-normal.woff2', false)
        ->assertSee('/fonts/schibsted-grotesk-latin-wght-normal.woff2', false)
        ->assertDontSee('fonts.googleapis.com', false);
});

test('le profil affiche la disponibilité en puce et le compteur de cauris', function () {
    $dev = User::factory()->withProfile(['availability' => Availability::Available])->create();

    $this->get(route('devs.show', $dev))
        ->assertSee('Disponible')
        ->assertSee('Pas encore de cauris');

    $dev->forceFill(['reputation' => 1240])->save();

    $this->get(route('devs.show', $dev))
        ->assertSee("1\u{202F}240")
        ->assertSee('cauris de réputation');
});

test('l\'avatar par défaut est rond avec les initiales', function () {
    $dev = User::factory()->withProfile()->create(['name' => 'Moussa Ndiaye']);

    $this->get(route('devs.show', $dev))->assertSee('>MN</span>', false);
});
