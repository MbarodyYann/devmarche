<?php

use App\Enums\MissionStatus;
use App\Livewire\Missions\Index;
use App\Models\Mission;
use App\Models\Skill;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $laravel = Skill::create(['name' => 'Laravel']);
    $flutter = Skill::create(['name' => 'Flutter']);

    $this->wave = Mission::factory()->create(['title' => 'Intégration Wave à Dakar', 'country' => 'SN', 'budget_fcfa' => 150000, 'created_at' => now()->subDays(2)]);
    $this->wave->skills()->attach($laravel);

    $this->tontine = Mission::factory()->remote()->create(['title' => 'Appli de tontine', 'budget_fcfa' => 350000, 'created_at' => now()->subDay()]);
    $this->tontine->skills()->attach($flutter);

    $this->resto = Mission::factory()->create(['title' => 'Site de restaurant', 'country' => 'CI', 'budget_fcfa' => 80000]);

    $this->done = Mission::factory()->create(['title' => 'Mission déjà terminée', 'country' => 'SN']);
    $this->done->forceFill(['status' => MissionStatus::Completed])->save();
});

test('la liste affiche les missions ouvertes par défaut, les plus récentes en premier', function () {
    $this->get('/missions')
        ->assertOk()
        ->assertSeeInOrder(['Site de restaurant', 'Appli de tontine', 'Intégration Wave à Dakar'])
        ->assertDontSee('Mission déjà terminée');
});

test('filtre par compétence', function () {
    Livewire::test(Index::class)->set('skill', 'laravel')
        ->assertSee('Intégration Wave à Dakar')->assertDontSee('Appli de tontine');
});

test('filtre par pays et par télétravail', function () {
    Livewire::test(Index::class)->set('country', 'CI')
        ->assertSee('Site de restaurant')->assertDontSee('Intégration Wave à Dakar');

    Livewire::test(Index::class)->set('country', 'teletravail')
        ->assertSee('Appli de tontine')->assertDontSee('Site de restaurant');
});

test('filtre par budget minimum et maximum', function () {
    Livewire::test(Index::class)->set('budgetMin', '100 000')->set('budgetMax', '200000')
        ->assertSee('Intégration Wave à Dakar')
        ->assertDontSee('Appli de tontine')
        ->assertDontSee('Site de restaurant');
});

test('filtre par statut', function () {
    Livewire::test(Index::class)->set('status', 'completed')
        ->assertSee('Mission déjà terminée')->assertDontSee('Appli de tontine');

    Livewire::test(Index::class)->set('status', 'tous')
        ->assertSee('Mission déjà terminée')->assertSee('Appli de tontine');
});

test('tri par budget décroissant', function () {
    // Requête HTTP : assertSeeInOrder de Livewire compare le JSON encodé (accents échappés).
    $this->get('/missions?tri=budget')
        ->assertSeeInOrder(['Appli de tontine', 'Intégration Wave à Dakar', 'Site de restaurant']);
});

test('les filtres sont lus depuis l\'URL', function () {
    $this->get('/missions?pays=teletravail')->assertSee('Appli de tontine')->assertDontSee('Site de restaurant');
});

test('la carte mission affiche le budget en FCFA et le nombre de candidatures', function () {
    $this->get('/missions')
        ->assertSee("150\u{202F}000")
        ->assertSee('FCFA / mission')
        ->assertSee('0 candidature');
});

test('un état vide s\'affiche quand rien ne correspond', function () {
    Livewire::test(Index::class)->set('country', 'TG')->assertSee('Aucune mission ne correspond');
});

test('la fiche d\'une mission est publique', function () {
    $this->get(route('missions.show', $this->wave))
        ->assertOk()
        ->assertSee('Intégration Wave à Dakar')
        ->assertSee('Décrochez cette mission');

    $this->get('/missions/abc')->assertNotFound();
});

test('« pour moi » ne montre que les missions qui correspondent aux compétences du membre', function () {
    $dev = User::factory()->withProfile()->create();
    $dev->skills()->attach(Skill::where('slug', 'flutter')->sole());

    $this->actingAs($dev);

    Livewire::test(Index::class)->set('forMe', true)
        ->assertSee('Appli de tontine')
        ->assertDontSee('Intégration Wave à Dakar')
        ->assertSee('Selon vos compétences');
});
