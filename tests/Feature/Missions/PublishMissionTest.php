<?php

use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Models\Skill;
use App\Models\User;

function missionData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Intégration de l\'API de paiement Wave',
        'description' => 'Notre boutique Laravel doit encaisser via Wave : session de paiement, webhook signé, mise à jour des commandes.',
        'budget_fcfa' => '150 000',
        'country' => 'SN',
        'deadline' => now()->addWeeks(3)->toDateString(),
        'skills' => 'Wave API, Laravel, laravel',
    ], $overrides);
}

test('un visiteur est redirigé vers la connexion pour publier', function () {
    $this->get(route('missions.create'))->assertRedirect('/login');
    $this->post(route('missions.store'), missionData())->assertRedirect('/login');
    expect(Mission::count())->toBe(0);
});

test('un membre publie une mission et arrive sur sa fiche', function () {
    $client = User::factory()->withProfile(['country' => 'SN'])->create();

    $this->actingAs($client)->get(route('missions.create'))->assertOk()->assertSee('Publier une mission');

    $response = $this->actingAs($client)->post(route('missions.store'), missionData());

    $mission = Mission::with('skills')->sole();
    $response->assertRedirect(route('missions.show', $mission))
        ->assertSessionHas('status', 'Mission publiée. Les devs arrivent !');

    expect($mission->client_id)->toBe($client->id)
        ->and($mission->budget_fcfa)->toBe(150000)
        ->and($mission->status)->toBe(MissionStatus::Open)
        ->and($mission->country)->toBe('SN')
        ->and($mission->skills->pluck('name')->all())->toBe(['Laravel', 'Wave API']);
});

test('une mission en télétravail n\'a pas de pays', function () {
    $client = User::factory()->withProfile()->create();

    $this->actingAs($client)->post(route('missions.store'), missionData(['remote' => '1', 'country' => '']))
        ->assertSessionHasNoErrors();

    expect(Mission::sole()->country)->toBeNull();
});

test('les compétences existantes sont réutilisées', function () {
    $wave = Skill::create(['name' => 'Wave API']);
    $client = User::factory()->withProfile()->create();

    $this->actingAs($client)->post(route('missions.store'), missionData(['skills' => 'wave api']));

    expect(Mission::sole()->skills->sole()->id)->toBe($wave->id);
});

test('la publication valide les champs côté serveur', function (array $data, string $field) {
    $client = User::factory()->withProfile()->create();

    $this->actingAs($client)->post(route('missions.store'), missionData($data))->assertSessionHasErrors($field);

    expect(Mission::count())->toBe(0);
})->with([
    'titre trop court' => [['title' => 'Site web'], 'title'],
    'description trop courte' => [['description' => 'Un site.'], 'description'],
    'budget sous 5 000 FCFA' => [['budget_fcfa' => '4 999'], 'budget_fcfa'],
    'budget non numérique' => [['budget_fcfa' => 'beaucoup'], 'budget_fcfa'],
    'pays manquant hors télétravail' => [['country' => ''], 'country'],
    'pays inconnu' => [['country' => 'ZZ'], 'country'],
    'échéance passée' => [['deadline' => now()->subDay()->toDateString()], 'deadline'],
    'trop de compétences' => [['skills' => implode(',', range('a', 'i'))], 'skills'],
]);

test('la publication est limitée à 10 missions par heure et par membre', function () {
    $client = User::factory()->withProfile()->create();

    foreach (range(1, 10) as $i) {
        $this->actingAs($client)->post(route('missions.store'), missionData(['title' => "Mission numéro $i à réaliser"]));
    }

    $this->actingAs($client)->post(route('missions.store'), missionData())->assertStatus(429);
    expect(Mission::count())->toBe(10);
});
