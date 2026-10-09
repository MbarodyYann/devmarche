<?php

use App\Models\Mission;
use App\Support\Copy;
use Database\Seeders\DatabaseSeeder;

/*
| Un seul CTA « Créer mon profil gratuit » visible à la fois : le CTA de la navigation suit
| le CTA de la page (data-page-cta) et reste masqué tant que celui-ci est à l'écran.
*/

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

function navCta(string $html): string
{
    preg_match('/<div[^>]*data-nav-cta[^>]*>/s', $html, $match);

    return $match[0] ?? '';
}

test('le CTA de la navigation suit le CTA de la page sur les pages qui en ont un', function (string $url) {
    $html = $this->get($url)->assertOk()->getContent();

    expect($html)->toContain('data-page-cta');
    expect(navCta($html))
        ->toContain('data-follows-page-cta')
        ->toContain('x-data="{ shown: false }"')
        ->toContain("document.querySelector('[data-page-cta]')")
        ->toContain('IntersectionObserver');
})->with(['/', '/missions', '/questions', '/devs']);

test('sans CTA de page, le CTA de la navigation reste visible', function () {
    $html = $this->get('/missions/'.Mission::first()->id)->assertOk()->getContent();

    expect($html)->not->toContain('data-page-cta');
    expect(navCta($html))->not->toBe('')->not->toContain('data-follows-page-cta');
});

test('la connexion reste toujours visible dans la navigation', function (string $url) {
    $html = $this->get($url)->getContent();

    expect($html)->toMatch('#<div class="hidden md:block">\s*<a[^>]*href="'.preg_quote(route('login'), '#').'"#');
})->with(['/', '/questions']);

test('le masquage ne s\'applique qu\'avec JS (amélioration progressive)', function () {
    expect($this->get('/')->getContent())->toContain("document.documentElement.classList.add('js')");
    expect(file_get_contents(resource_path('css/app.css')))
        ->toContain('.js [data-follows-page-cta]:not(.is-shown)')
        ->toContain('prefers-reduced-motion');
});

test('pas de lien recruteur sur les questions-réponses', function () {
    $this->get('/questions')->assertSee(Copy::SIGNUP_CTA)->assertDontSee(Copy::RECRUIT_LINK);
});

test('le lien recruteur reste sur l\'accueil, les missions et l\'annuaire', function (string $url) {
    $this->get($url)->assertSee(Copy::RECRUIT_LINK);
})->with(['/', '/missions', '/devs']);
