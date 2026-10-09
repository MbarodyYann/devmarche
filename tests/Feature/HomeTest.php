<?php

use App\Models\Mission;
use App\Models\Profile;
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
        ->assertSee('Quittez les groupes WhatsApp,')
        ->assertSee('<em class="block font-serif italic text-secondary-400">les missions viennent à vous.</em>', false)
        ->assertSee('vous postulez en 2 minutes, et vous êtes payé en Mobile Money.');
});

test('pour un visiteur, le CTA est identique dans le hero, la navigation, « Comment ça marche » et le CTA final', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect(substr_count($html, Copy::SIGNUP_CTA))->toBe(4)
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

test('la bande de preuve sociale affiche les chiffres réels de la base, sans icône ni cauris', function () {
    $devs = User::whereHas('profile')->count();
    $countries = Profile::distinct()->count('country');
    $missions = Mission::count();

    $html = $this->get('/')->getContent();
    $band = substr($html, strpos($html, 'aria-label="DevMarché en chiffres"'), 3000);
    $band = substr($band, 0, strpos($band, '</section>'));

    expect($band)->toContain('data-stat="devs">'.$devs.'<')
        ->toContain('data-stat="countries">'.$countries.'<')
        ->toContain('data-stat="missions">'.$missions.'<')
        ->toContain('Wave · Orange Money · MTN MoMo')
        ->toContain('lg:grid-cols-4')
        ->not->toContain('Cauris')
        ->not->toContain('<svg')
        ->not->toContain('<img')
        ->not->toContain(Countries::flag('SN'))
        ->and(substr_count($band, '<dt'))->toBe(4);
});

test('le hero a une seule image, placée après le CTA, chargée en priorité, sans dégradé', function () {
    $html = $this->get('/')->getContent();
    $hero = heroHtml($html);

    expect(substr_count($hero, '<img'))->toBe(1)
        ->and(strpos($hero, '<img'))->toBeGreaterThan(strpos($hero, 'data-cta="principal"'))
        ->and($hero)->toContain('loading="eager" fetchpriority="high"')
        ->toContain('decoding="async"')
        ->toContain('alt="Deux développeurs travaillent ensemble devant un ordinateur"')
        ->toContain('width="960" height="540"')
        ->not->toContain('gradient');

    // Sur ordinateur, la photo 4:5 est préchargée en priorité haute.
    expect($html)->toContain('rel="preload" as="image" type="image/webp" media="(min-width: 768px)" fetchpriority="high"');
});

test('les variantes de l\'image existent et respectent leur budget de poids', function () {
    $budgets = ['portrait-480' => 40_000, 'portrait-800' => 80_000, 'paysage-640' => 40_000, 'paysage-960' => 70_000];

    foreach ($budgets as $file => $max) {
        expect(public_path("images/hero/$file.webp"))->toBeFile()
            ->and(filesize(public_path("images/hero/$file.webp")))->toBeLessThanOrEqual($max);
    }
});

test('plus aucune trace de la photo Unsplash', function () {
    expect($this->get('/')->getContent())->not->toContain('unsplash')->not->toContain('devs-portrait');
});

test('l\'inscription reste courte : nom, e-mail, mot de passe, pays, ville', function () {
    $html = $this->get('/register')->getContent();

    preg_match_all('/<(input|select|textarea)[^>]*name="([^"]+)"/', $html, $fields);

    expect(collect($fields[2])->reject(fn ($f) => in_array($f, ['_token', 'q', 'remember'], true))->values()->all())
        ->toBe(['name', 'email', 'country', 'city', 'password', 'password_confirmation']);
});

test('la section des dernières missions reprend les textes de 11', function () {
    $this->get('/')
        ->assertSee('Missions ouvertes')
        ->assertSee('Des clients cherchent un dev en ce moment.')
        ->assertSee('Chaque mission affiche son budget en FCFA : postulez avec votre prix, le client choisit sur votre profil.')
        ->assertSee('Voir toutes les missions →');
});

test('le hero s\'ouvre directement sur le titre, sans surtitre', function () {
    $hero = heroHtml($this->get('/')->getContent());

    expect($hero)->not->toContain('Par et pour les devs')
        ->and(strpos($hero, '<h1'))->toBeLessThan(strpos($hero, 'Quittez'));
});
