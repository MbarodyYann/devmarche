<?php

use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Models\User;
use App\Support\Copy;
use App\Support\Credits;
use App\Support\Demo;
use App\Support\Money;

/*
| Passe visuelle (V1 à V9) : ce qui se vérifie dans le HTML et le CSS.
*/

test('V1 : verre de la navigation à 90 % (clair et sombre)', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain('color-mix(in srgb, var(--color-bg-alt) 90%, transparent)')
        ->toContain('color-mix(in srgb, var(--color-primary-950) 90%, transparent)')
        ->not->toContain('var(--color-bg-alt) 60%');
});

test('V2 : barre du bas opaque', function () {
    $html = $this->get('/missions')->getContent();

    expect($html)->toContain('<ul class="grid grid-cols-5 rounded-2xl border border-line bg-surface p-1.5 shadow-card-hover">');
});

test('V3 : sous 1 024 px, la bascule de thème est dans le pied de page, et le nom reste dans l\'en-tête', function () {
    $html = $this->get('/')->getContent();
    $footer = substr($html, strpos($html, '<footer'));

    expect(substr_count($html, 'aria-label="Basculer le thème sombre"'))->toBe(2)
        ->and($html)->toContain('max-lg:hidden')
        ->and($footer)->toContain('aria-label="Basculer le thème sombre"')
        ->and($html)->toContain('h-[17px] max-[359px]:hidden lg:h-6');
});

test('V4 : barre d\'action collante sur la fiche mission, selon le visiteur', function () {
    $aissatou = User::where('email', Demo::STARTUP_EMAIL)->sole();
    $mission = Mission::where('client_id', $aissatou->id)->where('status', MissionStatus::Open)->has('applications')->first();
    $count = $mission->applications()->count();

    // Visiteur : budget et CTA.
    $html = $this->get(route('missions.show', $mission))->getContent();
    $bar = substr($html, strpos($html, 'data-action-bar'), 2500);
    expect($bar)->toContain(Money::fcfa($mission->budget_fcfa))->toContain(Copy::SIGNUP_CTA)
        ->toContain("getElementById('postuler')")->toContain('IntersectionObserver')->toContain('motion-reduce:transition-none');

    // Client : « Voir les candidatures (N) ».
    $html = $this->actingAs($aissatou)->get(route('missions.show', $mission))->getContent();
    expect($html)->toContain('Voir les candidatures ('.$count.')')->toContain('id="candidatures"');

    // Dev qui n'a pas postulé : « Postuler à cette mission » vers #postuler.
    $dev = User::whereDoesntHave('applications', fn ($q) => $q->where('mission_id', $mission->id))->where('id', '!=', $aissatou->id)->first();
    $html = $this->actingAs($dev)->get(route('missions.show', $mission))->getContent();
    expect(substr($html, strpos($html, 'data-action-bar'), 2500))->toContain('href="#postuler"');

    // Mission terminée : pas de barre.
    $done = Mission::where('status', MissionStatus::Completed)->first();
    auth()->logout();
    $this->get(route('missions.show', $done))->assertDontSee('<div data-action-bar', false);
});

test('V5 : profil d\'un client pour un visiteur (missions publiées, ni sections vides, ni niveau, ni cauris)', function () {
    $aissatou = User::where('email', Demo::STARTUP_EMAIL)->sole();
    $html = $this->get(route('devs.show', $aissatou))->getContent();
    $stats = substr($html, strpos($html, 'data-profile-stats'), 1200);

    expect($stats)->toContain('Missions publiées')->not->toContain('Missions réalisées')
        ->and($html)->toContain('data-published-missions')
        ->not->toContain('>Réponses <')
        ->not->toContain('cauris de réputation')
        ->and($aissatou->level())->toBeNull();

    // Le propriétaire garde les sections avec l'invitation.
    $this->actingAs($aissatou)->get(route('devs.show', $aissatou))->assertSee('Réponses')->assertSee('Missions réalisées');
});

test('V6 : « Membre depuis » ne se coupe pas', function () {
    $mission = Mission::first();

    $this->get(route('missions.show', $mission))->assertSee('<span class="whitespace-nowrap">Membre depuis', false);
});

test('V7 : bande de confiance, avatars de 28 px et drapeaux séparés', function () {
    $html = $this->get('/')->getContent();
    $band = substr($html, strpos($html, 'aria-label="DevMarché en chiffres"'), 6000);

    expect(substr_count($band, 'size-7 text-[11px]'))->toBe(4)
        ->and($band)->toContain('ring-bg-alt!')
        ->toContain('flex flex-nowrap gap-0.5 text-base');
});

test('V8 : outils de développement repliés, tableau complet', function () {
    $all = collect(Credits::licenses());
    $html = $this->get('/a-propos')->getContent();

    expect($html)->toContain('Outils de développement ('.$all->where('dev', true)->count().')')
        ->and(substr_count($html, '<th scope="row"'))->toBe($all->count())
        ->and(strpos($html, 'PHPUnit'))->toBeGreaterThan(strpos($html, 'data-dev-tools'));
});

test('V9 : en sombre, le bandeau des listes se détache du fond (primary-900)', function () {
    $this->get('/missions')->assertSee('dark:bg-primary-900', false);
});

test('W1 : sous 1 024 px, l\'en-tête est opaque et sans fondu', function () {
    $css = file_get_contents(resource_path('css/app.css'));
    $nav = substr($css, strpos($css, '@utility nav-glass'), strpos($css, '@utility nav-menu') - strpos($css, '@utility nav-glass'));

    expect($nav)->not->toContain('97%')
        ->not->toContain('min-width: 768px')
        ->toContain('min-width: 1024px')
        ->and($this->get('/missions/1')->getContent())->toContain('lg:transition-colors')->not->toContain('px-2 transition-colors');
});

test('W2 : le titre d\'À propos fait 34 px sur mobile, aligné à gauche', function () {
    $html = $this->get('/a-propos')->getContent();

    expect($html)->toContain('md:text-center')->toContain('text-[34px] sm:text-[64px]');
});
