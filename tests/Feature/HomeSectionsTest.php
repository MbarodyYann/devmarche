<?php

use App\Enums\MissionStatus;
use App\Livewire\Missions\Index;
use App\Models\Mission;
use App\Models\Question;
use App\Models\Skill;
use App\Models\User;
use App\Support\Categories;
use App\Support\Copy;
use Database\Seeders\DatabaseSeeder;

/*
| Accueil complet (lot 4) : catégories populaires, missions ouvertes, « Comment ça marche »,
| questions résolues, CTA final ; pas de témoignages (règle 5 : aucune promesse fausse).
*/

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('les sections sont dans l\'ordre prévu, après la bande de preuve sociale', function () {
    $this->get('/')->assertOk()->assertSeeInOrder([
        'DevMarché en chiffres',
        'Votre spécialité a déjà ses clients.',
        'Des clients cherchent un dev en ce moment.',
        'Trois étapes entre vous et votre prochain client.',
        'Quelqu\'un a déjà eu votre bug.',
        'Votre prochaine mission n\'est pas dans un groupe WhatsApp.',
    ]);
});

test('6 catégories, chacune avec le nombre réel de missions ouvertes et un lien filtré', function () {
    $html = $this->get('/')->getContent();

    expect(substr_count($html, 'data-category='))->toBe(6);

    foreach (Categories::all() as $key => $category) {
        $expected = Mission::query()->where('status', MissionStatus::Open)
            ->whereHas('skills', fn ($q) => $q->whereIn('name', $category['skills']))->count();
        $label = $expected === 0 ? 'Pas de mission ouverte en ce moment' : $expected.' '.($expected > 1 ? 'missions ouvertes' : 'mission ouverte');

        expect($html)->toContain('href="'.e(route('missions.index', ['categorie' => $key])).'"')
            ->toContain($category['label'])
            ->toContain($label);
    }
});

test('le compteur d\'une catégorie suit les vraies missions ouvertes', function () {
    $before = Categories::openMissionCounts()['ussd-sms'];
    $client = User::first();
    $mission = Mission::factory()->for($client, 'client')->create(['status' => MissionStatus::Open]);
    $mission->skills()->attach(Skill::where('name', 'USSD')->sole());

    expect(Categories::openMissionCounts()['ussd-sms'])->toBe($before + 1);
    $this->get('/')->assertSee(($before + 1).' missions ouvertes');
});

test('/missions?categorie= filtre par groupe de compétences', function () {
    $payment = Categories::find('paiement-mobile')['skills'];

    Livewire\Livewire::withQueryParams(['categorie' => 'paiement-mobile'])
        ->test(Index::class)
        ->assertSee('Paiement mobile')
        ->assertSee('toutes les catégories')
        ->tap(function ($component) use ($payment) {
            $missions = $component->instance()->missions;
            expect($missions->total())->toBeGreaterThan(0);
            foreach ($missions as $mission) {
                expect($mission->skills->pluck('name')->intersect($payment))->not->toBeEmpty();
            }
        });
});

test('une catégorie inconnue est ignorée', function () {
    $all = Mission::where('status', MissionStatus::Open)->count();

    Livewire\Livewire::withQueryParams(['categorie' => 'nimporte-quoi'])
        ->test(Index::class)
        ->tap(fn ($component) => expect($component->instance()->missions->total())->toBe($all));
});

test('« Comment ça marche » : section sombre, 3 étapes numérotées et le CTA', function () {
    $html = $this->get('/')->getContent();
    $start = strpos($html, 'aria-labelledby="etapes-titre"');
    $section = substr($html, $start, strpos($html, 'aria-labelledby="questions-titre"') - $start);

    expect($section)->toContain('bg-night')
        ->toContain('pattern id="quadrillage"')
        ->toContain('Créez votre profil')
        ->toContain('Compétences, ville, langues : les clients vous trouvent par ce qui compte pour eux.')
        ->toContain('Postulez avec votre prix')
        ->toContain('Un message, un prix en FCFA, un délai. Le client compare et choisit.')
        ->toContain('Livrez et gagnez des cauris')
        ->toContain('Chaque mission terminée vous rapporte +20 cauris et fait monter votre niveau.')
        ->toContain(Copy::SIGNUP_CTA)
        ->and(substr_count($section, 'data-step'))->toBe(3);
});

test('questions résolues : les 3 plus récentes avec une réponse acceptée', function () {
    $expected = Question::whereNotNull('accepted_answer_id')->latest()->limit(3)->pluck('title');
    $unresolved = Question::whereNull('accepted_answer_id')->latest()->first();

    $response = $this->get('/')->assertSee('Voir toutes les questions →');
    foreach ($expected as $title) {
        $response->assertSee($title);
    }
    $response->assertDontSee($unresolved->title);
});

test('pas de témoignages ni de dégradés sur l\'accueil', function () {
    $html = $this->get('/')->getContent();

    expect($html)->not->toContain('Témoignage')
        ->not->toContain('gradient');
});
