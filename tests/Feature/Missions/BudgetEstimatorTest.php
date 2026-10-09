<?php

use App\Livewire\Missions\BudgetEstimator;
use App\Models\Mission;
use App\Models\Skill;
use App\Models\User;
use App\Support\Money;
use Livewire\Livewire;

/*
| Estimateur de budget de /missions/publier (lot 6, point 5).
*/

function comparable(string $skill, int $budget, ?string $country = 'SN'): void
{
    Mission::factory()->create(['budget_fcfa' => $budget, 'country' => $country])
        ->skills()->attach(Skill::firstOrCreate(['slug' => Str::slug($skill)], ['name' => $skill]));
}

test('le budget médian des missions similaires s\'affiche à mesure qu\'on saisit les compétences', function () {
    foreach ([120_000, 180_000, 200_000, 260_000] as $budget) {
        comparable('Laravel', $budget);
    }

    Livewire::test(BudgetEstimator::class, ['country' => 'SN'])
        ->assertDontSee('budget médian')
        ->set('skills', 'Laravel')
        ->assertSee('Les missions similaires (Laravel, SN) ont un budget médian de', false)
        ->assertSee(Money::fcfa(190_000).' FCFA')
        ->assertSee('(4 missions)');
});

test('rien sous 3 missions comparables', function () {
    comparable('Svelte', 100_000);
    comparable('Svelte', 200_000);

    Livewire::test(BudgetEstimator::class)->set('skills', 'Svelte')->assertDontSee('budget médian');
});

test('sans assez de missions dans le pays, on compare partout', function () {
    comparable('Flutter', 300_000, 'CI');
    comparable('Flutter', 400_000, 'CM');
    comparable('Flutter', 500_000, null);

    Livewire::test(BudgetEstimator::class, ['country' => 'SN'])
        ->set('skills', 'Flutter')
        ->assertSee('Les missions similaires (Flutter) ont un budget médian de', false)
        ->assertSee(Money::fcfa(400_000).' FCFA');
});

test('le pays du formulaire est transmis au composant', function () {
    foreach ([100_000, 150_000, 200_000] as $budget) {
        comparable('Django', $budget, 'BJ');
    }

    Livewire::test(BudgetEstimator::class, ['country' => 'SN'])
        ->set('skills', 'Django')
        ->assertSee('(Django)', false)
        ->dispatch('mission-country-changed', country: 'BJ')
        ->assertSee('(Django, BJ)', false);
});

test('le formulaire de publication contient l\'estimateur, et les compétences sont toujours envoyées', function () {
    $client = User::factory()->withProfile(['country' => 'SN'])->create();

    $this->actingAs($client)->get(route('missions.create'))->assertOk()
        ->assertSee('name="skills"', false)
        ->assertSee('wire:model.live.debounce.500ms="skills"', false)
        ->assertSee("Livewire.dispatch('mission-country-changed'", false);

    $this->actingAs($client)->post(route('missions.store'), [
        'title' => 'Site vitrine pour une boulangerie',
        'description' => 'Un site simple avec les horaires, les produits du jour et un formulaire de commande.',
        'budget_fcfa' => '150 000',
        'country' => 'SN',
        'skills' => 'Laravel, Tailwind CSS',
    ])->assertRedirect();

    expect(Mission::latest('id')->first()->skills->pluck('name')->all())->toContain('Laravel', 'Tailwind CSS');
});
