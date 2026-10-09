<?php

use App\Enums\Availability;
use App\Livewire\Devs\Directory;
use App\Models\Skill;
use App\Models\User;
use Livewire\Livewire;

function makeDev(string $name, string $country, string $city, Availability $availability = Availability::Available, array $skills = [], int $reputation = 0): User
{
    $user = User::factory()
        ->withProfile(['country' => $country, 'city' => $city, 'availability' => $availability, 'headline' => null])
        ->create(['name' => $name, 'reputation' => $reputation]);

    foreach ($skills as $skill) {
        $user->skills()->attach(Skill::firstOrCreate(['name' => $skill]));
    }

    return $user;
}

beforeEach(function () {
    makeDev('Moussa Ndiaye', 'SN', 'Thiès', Availability::Available, ['Laravel', 'Wave API'], 120);
    makeDev('Fatou Sow', 'SN', 'Dakar', Availability::Busy, ['Flutter']);
    makeDev('Kouadio Yao', 'CI', 'Abidjan', Availability::Employed, ['Java', 'Laravel']);
    makeDev('Aya Koné', 'CI', 'Abidjan', Availability::Available, ['React Native']);
});

test('l\'annuaire s\'affiche et liste les devs, les plus réputés en premier', function () {
    $this->get('/devs')
        ->assertOk()
        ->assertSeeLivewire(Directory::class)
        ->assertSeeInOrder(['Moussa Ndiaye', 'Aya Koné', 'Fatou Sow', 'Kouadio Yao']);
});

test('l\'annuaire filtre par pays', function () {
    Livewire::test(Directory::class)
        ->set('country', 'CI')
        ->assertSee('Kouadio Yao')
        ->assertSee('Aya Koné')
        ->assertDontSee('Moussa Ndiaye')
        ->assertDontSee('Fatou Sow');
});

test('l\'annuaire filtre par ville sans tenir compte de la casse', function () {
    Livewire::test(Directory::class)
        ->set('city', 'thiès')
        ->assertSee('Moussa Ndiaye')
        ->assertDontSee('Fatou Sow')
        ->assertDontSee('Kouadio Yao');
});

test('l\'annuaire filtre par compétence', function () {
    Livewire::test(Directory::class)
        ->set('skill', 'laravel')
        ->assertSee('Moussa Ndiaye')
        ->assertSee('Kouadio Yao')
        ->assertDontSee('Fatou Sow')
        ->assertDontSee('Aya Koné');
});

test('l\'annuaire filtre par disponibilité', function () {
    Livewire::test(Directory::class)
        ->set('availability', 'busy')
        ->assertSee('Fatou Sow')
        ->assertDontSee('Moussa Ndiaye')
        ->assertDontSee('Kouadio Yao');
});

test('les filtres se combinent', function () {
    Livewire::test(Directory::class)
        ->set('country', 'CI')
        ->set('skill', 'laravel')
        ->assertSee('Kouadio Yao')
        ->assertDontSee('Aya Koné')
        ->assertDontSee('Moussa Ndiaye');
});

test('la recherche porte sur le nom et les compétences', function () {
    Livewire::test(Directory::class)
        ->set('q', 'flutter')
        ->assertSee('Fatou Sow')
        ->assertDontSee('Moussa Ndiaye');

    Livewire::test(Directory::class)
        ->set('q', 'kouadio')
        ->assertSee('Kouadio Yao')
        ->assertDontSee('Aya Koné');
});

test('les filtres sont lus depuis l\'URL', function () {
    $this->get('/devs?pays=SN&competence=wave-api')
        ->assertOk()
        ->assertSee('Moussa Ndiaye')
        ->assertDontSee('Fatou Sow')
        ->assertDontSee('Kouadio Yao');
});

test('changer de pays réinitialise la ville', function () {
    Livewire::test(Directory::class)
        ->set('city', 'Thiès')
        ->set('country', 'CI')
        ->assertSet('city', '')
        ->assertSee('Kouadio Yao');
});

test('un état vide s\'affiche quand aucun dev ne correspond', function () {
    Livewire::test(Directory::class)
        ->set('country', 'SN')
        ->set('availability', 'employed')
        ->assertSee('Aucun dev ne correspond à ces filtres.')
        ->call('resetFilters')
        ->assertSee('Moussa Ndiaye');
});

test('une valeur de filtre invalide est ignorée sans erreur', function () {
    $this->get('/devs?dispo=nimporte&pays=%27%3B--')->assertOk()->assertSee('Aucun dev ne correspond');
    $this->get('/devs?dispo=nimporte')->assertOk()->assertSee('Moussa Ndiaye');
});

test('l\'annuaire est paginé par 12', function () {
    foreach (range(1, 12) as $i) {
        makeDev("Dev Supplémentaire $i", 'BJ', 'Cotonou');
    }

    Livewire::test(Directory::class)
        ->assertViewHas('availabilities')
        ->assertSee('Page 1 / 2')
        ->call('nextPage')
        ->assertSee('Page 2 / 2');
});
