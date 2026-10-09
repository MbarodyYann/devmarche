<?php

use App\Models\Mission;
use App\Models\Skill;
use App\Models\User;
use App\Services\MissionService;
use App\Services\PulseService;
use App\Support\Money;
use Illuminate\Support\Facades\Cache;

/*
| Le Pouls (lot 6, point 4) : statistiques réelles, en cache 10 minutes, barres en CSS.
*/

beforeEach(fn () => Cache::flush());

function missionWith(array $skills, int $budget, ?string $country = 'SN', int $daysAgo = 2): Mission
{
    $mission = Mission::factory()->create(['budget_fcfa' => $budget, 'country' => $country, 'created_at' => now()->subDays($daysAgo)]);
    $mission->skills()->attach(collect($skills)->map(fn (string $name) => Skill::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id));

    return $mission;
}

test('la page affiche les chiffres réels', function () {
    missionWith(['Laravel'], 100_000);
    missionWith(['Laravel'], 200_000);
    missionWith(['Laravel', 'Wave API'], 300_000);
    missionWith(['Flutter'], 500_000, 'CI', 40); // hors des 30 jours

    $html = $this->get(route('pulse'))->assertOk()->getContent();

    expect($html)->toContain('Le marché des devs, <em class="font-serif italic text-secondary-400">en chiffres réels.</em>')
        ->toContain('data-pulse="missions">3<')
        ->toContain('data-pulse-list="skills"')
        ->toContain(Money::fcfa(200_000).' FCFA')   // médiane Laravel (3 missions)
        ->not->toContain('<canvas')
        ->not->toContain('chart.js');

    // Compétences des 30 derniers jours seulement : Flutter (il y a 40 jours) n'y figure pas.
    $skills = substr($html, strpos($html, 'data-pulse-list="skills"'), 3000);
    expect($skills)->toContain('Laravel')->not->toContain('Flutter');
});

test('budget médian : rien sous 3 missions comparables', function () {
    missionWith(['Django'], 100_000);
    missionWith(['Django'], 300_000);

    $pulse = app(PulseService::class);
    expect($pulse->medianFor(['Django']))->toBeNull()
        ->and(collect($pulse->medianBySkill())->pluck('name'))->not->toContain('Django');

    missionWith(['Django'], 200_000, 'CI');
    expect($pulse->medianFor(['Django']))->toBe(['median' => 200_000, 'count' => 3])
        ->and($pulse->medianFor(['django'], 'SN'))->toBeNull();
});

test('devs par pays et délai moyen avant la 1re candidature', function () {
    User::factory()->withProfile(['country' => 'SN'])->count(2)->create();
    User::factory()->withProfile(['country' => 'BJ'])->create();

    $mission = Mission::factory()->create(['created_at' => now()->subHours(5)]);
    $this->travelTo(now()->subHours(3), fn () => app(MissionService::class)->apply($mission, User::factory()->withProfile()->create(), ['message' => 'Dispo', 'proposed_fcfa' => 50_000, 'delivery_days' => 5]));

    $pulse = app(PulseService::class);
    expect(collect($pulse->devsByCountry())->firstWhere('country', 'SN')['count'])->toBeGreaterThanOrEqual(2)
        ->and($pulse->firstApplicationHours())->toEqual(2.0);
});

test('les chiffres sont mis en cache 10 minutes', function () {
    missionWith(['Laravel'], 100_000);
    $this->get(route('pulse'))->assertSee('data-pulse="missions">1<', false);

    missionWith(['Laravel'], 100_000);
    $this->get(route('pulse'))->assertSee('data-pulse="missions">1<', false);

    $this->travel(11)->minutes();
    $this->get(route('pulse'))->assertSee('data-pulse="missions">2<', false);
});

test('Le Pouls est dans la navigation et le plan du site', function () {
    $this->get('/')->assertSee('href="'.route('pulse').'"', false)->assertSee('Le Pouls');
    $this->get('/sitemap.xml')->assertSee(route('pulse'));
});
