<?php

use App\Enums\Availability;
use App\Models\User;
use Illuminate\Support\Facades\Blade;

test('la page 404 est en français', function () {
    $this->get('/cette-page-n-existe-pas')
        ->assertNotFound()
        ->assertSee('Cette page s\'est perdue en route.', false);
});

test('plus de recherche dans la navigation : chaque liste a la sienne', function () {
    $dev = User::factory()->withProfile()->create();

    $this->get(route('devs.show', $dev))->assertDontSee('id="recherche-globale"', false);

    foreach (['/missions', '/questions', '/devs'] as $url) {
        $this->get($url)->assertDontSee('id="recherche-globale"', false)->assertSee('id="filtre-q"', false);
    }
});

test('les polices auto-hébergées sont préchargées', function () {
    $this->get('/devs')
        ->assertSee('/fonts/newsreader-latin-wght-normal.woff2', false)
        ->assertSee('/fonts/schibsted-grotesk-latin-wght-normal.woff2', false)
        ->assertDontSee('fonts.googleapis.com', false);
});

test('le profil affiche la disponibilité en puce, et le compteur de cauris seulement s\'il y en a', function () {
    $dev = User::factory()->withProfile(['availability' => Availability::Available])->create();

    $this->get(route('devs.show', $dev))
        ->assertSee('Disponible')
        ->assertDontSee('cauris de réputation');

    $dev->forceFill(['reputation' => 1240])->save();

    $this->get(route('devs.show', $dev))
        ->assertSee("1\u{202F}240")
        ->assertSee('cauris de réputation');
});

test('l\'avatar par défaut est rond avec les initiales', function () {
    $dev = User::factory()->withProfile()->create(['name' => 'Moussa Ndiaye']);

    $this->get(route('devs.show', $dev))->assertSee('>MN</span>', false);
});

test('un bouton masqué sur mobile ne reçoit pas inline-flex par défaut', function () {
    $html = Blade::render('<x-button class="hidden sm:inline-flex" href="/x">Publier</x-button>');

    expect($html)->toContain('class="items-center')->toContain('hidden sm:inline-flex');

    // « sm:hidden » n'est pas un « hidden » : le bouton reste en inline-flex sur mobile.
    expect(Blade::render('<x-button class="sm:hidden">Ok</x-button>'))->toContain('class="inline-flex');
});

test('l\'annuaire affiche des cartes, plus de tableau', function () {
    User::factory()->withProfile()->count(3)->create();

    $this->get('/devs')
        ->assertOk()
        ->assertDontSee('<table', false)
        ->assertSee('grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3', false);
});

test('le texte défilant du pied de page est en couleurs pleines et lisible par les lecteurs d\'écran', function () {
    $html = $this->get('/devs')->getContent();
    $footer = substr($html, strpos($html, '<footer'));

    expect($footer)->toContain('<p class="sr-only">Du travail, de l\'aide, des pairs. Par et pour les devs d\'Afrique.</p>')
        ->toContain('italic text-primary-600 dark:text-secondary-400')
        ->not->toMatch('/text-(ink|primary-\d+|secondary-\d+)\/\d+/');
});
