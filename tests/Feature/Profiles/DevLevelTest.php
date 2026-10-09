<?php

use App\Enums\DevLevel;
use App\Enums\MissionStatus;
use App\Livewire\Devs\Directory;
use App\Models\Application;
use App\Models\Mission;
use App\Models\User;
use Livewire\Livewire;

function devWith(int $reputation, int $completed, int $daysAgo = 3, string $name = 'Dev'): User
{
    $user = User::factory()->withProfile()->create(['name' => $name, 'reputation' => $reputation, 'created_at' => now()->subDays($daysAgo)]);
    // Une activité de dev (candidature) : sans elle, aucun niveau n'est affiché.
    Application::factory()->for($user)->create();
    Mission::factory()->count($completed)->create()->each(fn (Mission $m) => $m->forceFill([
        'status' => MissionStatus::Completed, 'freelancer_id' => $user->id, 'assigned_at' => now(), 'completed_at' => now(),
    ])->save());

    return $user->fresh();
}

test('les niveaux sont calculés selon les cauris et les missions terminées', function (int $rep, int $completed, int $days, ?string $label) {
    expect(devWith($rep, $completed, $days)->level()?->label())->toBe($label);
})->with([
    'nouveau inscrit' => [0, 0, 3, 'Nouveau'],
    'ancien sans activité' => [20, 0, 30, null],
    'confirmé par les cauris' => [100, 0, 3, 'Confirmé'],
    'confirmé par une mission' => [0, 1, 3, 'Confirmé'],
    'cauris d\'expert mais 2 missions' => [800, 2, 3, 'Confirmé'],
    'expert' => [500, 3, 3, 'Expert'],
    'référence' => [1000, 5, 3, 'Référence'],
]);

test('le niveau s\'affiche en étiquette texte sur les cartes et le profil', function () {
    $expert = devWith(600, 3, 10, 'Awa Experte');

    $this->get('/devs')->assertSee('Expert');
    $this->get(route('devs.show', $expert))->assertSee('Expert');
});

test('l\'annuaire filtre par niveau exact', function () {
    devWith(1200, 6, 10, 'Ref Erence');
    devWith(600, 3, 10, 'Ex Pert');
    devWith(150, 0, 10, 'Con Firmé');
    devWith(0, 0, 2, 'Nou Veau');

    Livewire::test(Directory::class)->set('level', 'expert')
        ->assertSee('Ex Pert')->assertDontSee('Ref Erence')->assertDontSee('Con Firmé');

    Livewire::test(Directory::class)->set('level', 'nouveau')
        ->assertSee('Nou Veau')->assertDontSee('Con Firmé');

    Livewire::test(Directory::class)->set('level', 'confirme')
        ->assertSee('Con Firmé')->assertDontSee('Ex Pert');
});

test('les options du filtre sont dans l\'ordre décroissant', function () {
    expect(array_values(DevLevel::options()))->toBe(['Référence', 'Expert', 'Confirmé', 'Nouveau']);
});

test('un client pur (ni réponse ni candidature) n\'a pas d\'étiquette de niveau', function () {
    $client = User::factory()->withProfile()->create(['created_at' => now()->subDays(2)]);
    Mission::factory()->for($client, 'client')->create();

    expect($client->level())->toBeNull()
        ->and($client->isNewMember())->toBeTrue();

    Livewire::test(Directory::class)->set('level', 'nouveau')->assertDontSee($client->name);
});
