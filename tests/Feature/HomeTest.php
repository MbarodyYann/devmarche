<?php

use App\Enums\MissionStatus;
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

test('l\'accueil vend la transformation : la douleur, puis le mécanisme', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Arrêtez de chercher vos missions')
        ->assertSee('WhatsApp</span>.', false)
        ->assertSee('vous postulez en 2 minutes, et vous êtes payé en Mobile Money.');
});

test('pour un visiteur, le CTA est le même mot pour mot dans le hero, la navigation et le bloc final', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect(substr_count($html, Copy::SIGNUP_CTA))->toBe(3)
        ->and($html)->toContain('Vous recrutez ? Publier une mission →')
        ->toContain(Copy::REASSURANCE_RISK)
        ->toContain(Copy::REASSURANCE_NEXT)
        ->toContain('Votre prochaine mission n\'est pas dans un groupe WhatsApp.')
        ->not->toContain('Créer un compte');
});

test('le hero n\'a qu\'un seul bouton principal', function () {
    $html = $this->get('/')->getContent();
    $hero = substr($html, 0, strpos($html, 'Pensé pour le Mobile Money'));

    expect(substr_count($hero, 'data-cta="principal"'))->toBe(1)
        ->and(substr_count($hero, 'btn-accent'))->toBe(0);
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

test('la preuve sociale affiche les chiffres réels de la base', function () {
    $devs = User::whereHas('profile')->count();
    $countries = Profile::distinct()->count('country');
    $completed = Mission::where('status', MissionStatus::Completed)->count();

    $this->get('/')
        ->assertSee("Rejoignez <strong class=\"font-semibold text-ink\">$devs devs</strong>", false)
        ->assertSee("$countries pays</strong>", false)
        ->assertSee("$completed missions</strong> terminées", false)
        ->assertSee(Countries::flag('SN'));
});

test('sans mission terminée, la preuve sociale ne parle pas de missions', function () {
    Mission::query()->update(['status' => MissionStatus::Open]);

    $this->get('/')->assertDontSee('terminées');
});

test('le produit en action montre la mission Wave d\'Aïssatou et Moussa', function () {
    $moussa = User::where('email', DevSeeder::DEMO_DEV_EMAIL)->sole();

    $this->get('/')
        ->assertSee('Intégration de l&#039;API de paiement Wave', false)
        ->assertSee('Candidature retenue')
        ->assertSee($moussa->name)
        ->assertSee('+20 cauris')
        ->assertSee('Pensé pour le Mobile Money')
        ->assertSee('Moov Money');
});

test('l\'accueil n\'a aucune image au-dessus de la ligne de flottaison', function () {
    $html = $this->get('/')->getContent();
    $hero = substr($html, 0, strpos($html, 'Pensé pour le Mobile Money'));

    expect($hero)->not->toContain('<img');
});

test('l\'inscription reste courte : nom, e-mail, mot de passe, pays, ville', function () {
    $html = $this->get('/register')->getContent();

    preg_match_all('/<(input|select|textarea)[^>]*name="([^"]+)"/', $html, $fields);

    expect(collect($fields[2])->reject(fn ($f) => in_array($f, ['_token', 'q', 'remember'], true))->values()->all())
        ->toBe(['name', 'email', 'country', 'city', 'password', 'password_confirmation']);
});
