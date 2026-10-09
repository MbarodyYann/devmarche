<?php

use App\Models\Mission;
use App\Models\User;
use App\Support\Copy;

/*
| CTA « Créer mon profil gratuit » (décision du représentant) : celui de la navigation est visible
| en permanence pour un visiteur, sur toutes les pages. L'accueil garde aussi le CTA de son hero ;
| les pages de liste n'ont aucun CTA dans leur en-tête compact.
*/

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
        ->and(substr_count($html, Copy::SIGNUP_CTA))->toBe(2); // navigation : ordinateur et mobile
})->with(['/missions', '/questions', '/devs']);

test('la connexion reste toujours visible dans la navigation', function (string $url) {
    expect($this->get($url)->getContent())->toMatch('#<div class="hidden lg:block">\s*<a[^>]*href="'.preg_quote(route('login'), '#').'"#');
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

test('sous 1 024 px, un petit bouton ambre dans la navigation, et le logo réduit au symbole', function (string $url) {
    $html = $this->get($url)->getContent();

    preg_match('/<div class="lg:hidden" data-nav-cta-mobile>\s*<a([^>]*)>([^<]*)<\/a>/', $html, $match);

    expect($match)->not->toBeEmpty()
        ->and(trim($match[2]))->toBe(Copy::SIGNUP_CTA)
        ->and($match[1])->toContain('btn-amber')->toContain('min-h-9')->toContain('text-[13px]')
        ->and($html)->toContain('max-lg:hidden');
})->with(['/', '/missions', '/a-propos']);

test('pour un membre, ni bouton mobile ni logo réduit', function () {
    $html = $this->actingAs(User::first())->get('/missions')->getContent();

    expect($html)->not->toContain('data-nav-cta-mobile')->not->toContain('max-lg:hidden');
});

test('« Comment ça marche » : bouton aligné à droite sur ordinateur, pleine largeur sur mobile', function () {
    $html = $this->get('/')->getContent();
    $start = strpos($html, 'aria-labelledby="etapes-titre"');
    $section = substr($html, $start, strpos($html, 'aria-labelledby="questions-titre"') - $start);

    expect($section)->toContain('md:flex md:justify-end')->toContain('w-full sm:w-auto');
});
