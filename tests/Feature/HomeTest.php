<?php

use App\Models\Mission;
use App\Models\Profile;
use App\Models\ReputationEvent;
use App\Models\User;
use App\Support\Copy;
use App\Support\Countries;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DevSeeder;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    Cache::flush();
});

function heroHtml(string $html): string
{
    return substr($html, strpos($html, 'id="hero"'), strpos($html, 'aria-label="Nos garanties"') - strpos($html, 'id="hero"'));
}

test('le hero nomme la douleur puis vend la transformation, et explique le mécanisme', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Par et pour les devs d\'Afrique francophone', false)
        ->assertDontSee('— Par et pour')
        ->assertSee('Quittez les groupes WhatsApp,')
        ->assertSee('<em class="block font-serif italic text-secondary-400">les missions viennent à vous.</em>', false)
        ->assertSee('vous postulez en 2 minutes, et vous êtes payé en Mobile Money.');
});

test('pour un visiteur, le CTA est identique dans le hero, la navigation et le CTA final', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect(substr_count($html, Copy::SIGNUP_CTA))->toBe(3)
        ->and($html)->toContain('Vous recrutez ? Publier une mission →')
        ->toContain('Votre prochaine mission n\'est pas dans un groupe WhatsApp.')
        ->not->toContain('Créer un compte');
});

test('le hero n\'a qu\'un seul bouton principal, en ambre', function () {
    $hero = heroHtml($this->get('/')->getContent());

    expect(substr_count($hero, 'data-cta="principal"'))->toBe(1)
        ->and(substr_count($hero, 'btn-cta'))->toBe(1)
        ->and(substr_count($hero, 'btn-primary'))->toBe(0);
});

test('pour un membre connecté, le CTA mène aux missions qui lui correspondent', function () {
    $user = User::where('email', DevSeeder::DEMO_DEV_EMAIL)->sole();

    $this->actingAs($user)->get('/')
        ->assertOk()
        ->assertSee(Copy::MEMBER_CTA)
        ->assertSee(route('missions.index', ['pour_moi' => 1]), false)
        ->assertSee('Publier une mission →')
        ->assertDontSee(Copy::SIGNUP_CTA);
});

test('la bande de garanties reprend les trois promesses', function () {
    $this->get('/')
        ->assertSee('Payé en Mobile Money')
        ->assertSee(Copy::REASSURANCE_RISK)
        ->assertSee('Une réputation qui vous suit');
});

test('la bande de preuve sociale affiche les chiffres réels de la base', function () {
    $devs = User::whereHas('profile')->count();
    $countries = Profile::distinct()->count('country');
    $missions = Mission::count();
    $cauris = (int) ReputationEvent::where('points', '>', 0)->sum('points');

    $this->get('/')
        ->assertSee('data-stat="devs">'.$devs.'<', false)
        ->assertSee('data-stat="countries">'.$countries.'<', false)
        ->assertSee('data-stat="missions">'.$missions.'<', false)
        ->assertSee('data-stat="cauris">'.number_format($cauris, 0, ',', "\u{202F}").'<', false)
        ->assertSee(Countries::flag('SN'))
        ->assertSee('Wave · Orange Money · MTN MoMo');
});

test('le hero a une seule photo, placée après le CTA, chargée en différé, sans dégradé', function () {
    $html = $this->get('/')->getContent();
    $hero = heroHtml($html);

    expect(substr_count($hero, '<img'))->toBe(1)
        ->and(strpos($hero, '<img'))->toBeGreaterThan(strpos($hero, 'data-cta="principal"'))
        ->and($hero)->toContain('loading="lazy"')
        ->toContain('decoding="async"')
        ->toContain('alt="Deux développeurs travaillent ensemble sur du code"')
        ->toContain('width="800" height="450"')
        ->not->toContain('gradient');

    // Sur ordinateur, la photo 4:5 est préchargée en priorité haute.
    expect($html)->toContain('rel="preload" as="image" type="image/webp" media="(min-width: 1024px)" fetchpriority="high"');
});

test('les variantes de la photo existent et respectent leur budget de poids', function () {
    foreach (['portrait', 'paysage'] as $crop) {
        expect(filesize(public_path("images/hero/devs-$crop-480.webp")))->toBeLessThanOrEqual(40_000)
            ->and(filesize(public_path("images/hero/devs-$crop-800.webp")))->toBeLessThanOrEqual(80_000);
    }
});

test('le crédit de la photo figure dans le pied de page', function () {
    $this->get('/')->assertSee('Photo : <a href="https://unsplash.com/photos/kwzWjTnDPLk"', false);
});

test('l\'inscription reste courte : nom, e-mail, mot de passe, pays, ville', function () {
    $html = $this->get('/register')->getContent();

    preg_match_all('/<(input|select|textarea)[^>]*name="([^"]+)"/', $html, $fields);

    expect(collect($fields[2])->reject(fn ($f) => in_array($f, ['_token', 'q', 'remember'], true))->values()->all())
        ->toBe(['name', 'email', 'country', 'city', 'password', 'password_confirmation']);
});
