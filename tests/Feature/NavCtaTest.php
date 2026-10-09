<?php

use App\Models\Mission;
use App\Support\Copy;
use Database\Seeders\DatabaseSeeder;

/*
| CTA « Créer mon profil gratuit » (décision du représentant) : celui de la navigation est visible
| en permanence pour un visiteur, sur toutes les pages. L'accueil garde aussi le CTA de son hero ;
| les pages de liste n'ont aucun CTA dans leur en-tête compact.
*/

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

function navCta(string $html): string
{
    preg_match('/<div[^>]*data-nav-cta[^>]*>\s*<a[^>]*>[^<]*<\/a>/s', $html, $match);

    return $match[0] ?? '';
}

test('le CTA de la navigation est affiché dès le chargement, sans masquage', function (string $url) {
    $html = $this->get($url === 'fiche' ? route('missions.show', Mission::first()) : $url)->assertOk()->getContent();

    expect(navCta($html))->toContain(Copy::SIGNUP_CTA)->toContain('href="'.route('register').'"')
        ->not->toContain('x-data')->not->toContain('x-show')->not->toContain('x-cloak')
        ->and($html)->not->toContain('data-follows-page-cta')
        ->not->toContain('data-page-cta')
        ->not->toContain('IntersectionObserver');
})->with(['/', '/missions', '/questions', '/devs', 'fiche', '/a-propos']);

test('l\'accueil garde le CTA de son hero, avec le lien recruteur dessous', function () {
    $html = $this->get('/')->getContent();
    $hero = substr($html, strpos($html, 'id="hero"'), strpos($html, 'aria-label="Nos garanties"') - strpos($html, 'id="hero"'));

    expect($hero)->toContain(Copy::SIGNUP_CTA)
        ->and(strpos($hero, Copy::RECRUIT_LINK))->toBeGreaterThan(strpos($hero, Copy::SIGNUP_CTA));
});

test('pages de liste : aucun CTA dans l\'en-tête compact, seulement celui de la navigation', function (string $url) {
    $html = $this->get($url)->getContent();
    $hero = substr($html, strpos($html, 'id="hero"'), strpos($html, '<main') - strpos($html, 'id="hero"'));

    expect($hero)->not->toContain(Copy::SIGNUP_CTA)->not->toContain(Copy::RECRUIT_LINK)
        ->and(substr_count($html, Copy::SIGNUP_CTA))->toBe(1);
})->with(['/missions', '/questions', '/devs']);

test('la connexion reste toujours visible dans la navigation', function (string $url) {
    expect($this->get($url)->getContent())->toMatch('#<div class="hidden md:block">\s*<a[^>]*href="'.preg_quote(route('login'), '#').'"#');
})->with(['/', '/questions']);

test('le lien recruteur n\'apparaît que sur l\'accueil', function () {
    $this->get('/')->assertSee(Copy::RECRUIT_LINK);

    foreach (['/missions', '/questions', '/devs'] as $url) {
        $this->get($url)->assertDontSee(Copy::RECRUIT_LINK);
    }
});

test('plus aucune règle CSS de masquage du CTA de la navigation', function () {
    expect(file_get_contents(resource_path('css/app.css')))->not->toContain('data-follows-page-cta');
});
