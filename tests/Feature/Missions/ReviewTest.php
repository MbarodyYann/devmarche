<?php

use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Models\Review;
use App\Models\User;
use App\Support\Money;

/*
| Avis enrichis (lot 6) : le client note le freelance une fois, sur une mission terminée ;
| bonus de réputation ; statistiques et liste sur le profil ; « ★ 4,8 (6) » dans les cartes.
*/

function completedMission(?User $client = null, ?User $dev = null, int $budget = 180_000, int $days = 12): Mission
{
    $client ??= User::factory()->withProfile()->create();
    $dev ??= User::factory()->withProfile()->create();
    $mission = Mission::factory()->for($client, 'client')->create(['budget_fcfa' => $budget]);
    $mission->forceFill([
        'status' => MissionStatus::Completed,
        'freelancer_id' => $dev->id,
        'assigned_at' => now()->subDays($days + 1),
        'completed_at' => now()->subDay(),
    ])->save();

    return $mission->fresh();
}

function reviewData(int $rating = 5, array $extra = []): array
{
    return array_merge(['rating' => $rating, 'communication' => 5, 'quality' => 4, 'timeliness' => 5, 'comment' => 'Travail propre et livré à temps.'], $extra);
}

test('le client note le freelance d\'une mission terminée, avec le bonus de réputation', function (int $rating, int $bonus) {
    $mission = completedMission();
    $dev = $mission->freelancer;

    $this->actingAs($mission->client)->post(route('missions.review', $mission), reviewData($rating))
        ->assertRedirect(route('missions.show', $mission))
        ->assertSessionHas('status');

    $review = Review::sole();
    expect($review->reviewer_id)->toBe($mission->client_id)
        ->and($review->reviewee_id)->toBe($dev->id)
        ->and($review->rating)->toBe($rating)
        ->and($dev->fresh()->reputation)->toBe($bonus);

    if ($bonus > 0) {
        $this->assertDatabaseHas('reputation_events', ['user_id' => $dev->id, 'points' => $bonus, 'reason' => 'review_bonus', 'source_type' => 'review']);
    }
})->with([
    '5 étoiles' => [5, 30],
    '4 étoiles' => [4, 20],
    '3 étoiles' => [3, 10],
    '2 étoiles' => [2, 0],
]);

test('une seule fois par mission', function () {
    $mission = completedMission();
    $this->actingAs($mission->client)->post(route('missions.review', $mission), reviewData());

    $this->actingAs($mission->client)->post(route('missions.review', $mission), reviewData(4))->assertForbidden();
    expect(Review::count())->toBe(1);
});

test('seul le client peut noter, et seulement une mission terminée', function () {
    $mission = completedMission();

    $this->actingAs($mission->freelancer)->post(route('missions.review', $mission), reviewData())->assertForbidden();
    $this->actingAs(User::factory()->create())->post(route('missions.review', $mission), reviewData())->assertForbidden();

    $assigned = completedMission();
    $assigned->forceFill(['status' => MissionStatus::Assigned, 'completed_at' => null])->save();
    $this->actingAs($assigned->client)->post(route('missions.review', $assigned), reviewData())->assertForbidden();

    auth()->logout();
    $this->post(route('missions.review', $mission), reviewData())->assertRedirect(route('login'));
    expect(Review::count())->toBe(0);
});

test('les notes vont de 1 à 5, le commentaire est facultatif', function () {
    $mission = completedMission();

    $this->actingAs($mission->client)->post(route('missions.review', $mission), reviewData(6, ['quality' => 0]))
        ->assertSessionHasErrors(['rating', 'quality']);
    $this->actingAs($mission->client)->post(route('missions.review', $mission), reviewData(5, ['comment' => null]))
        ->assertSessionHasNoErrors();
});

test('le formulaire d\'avis est accessible : un groupe de radios par note', function () {
    $mission = completedMission();
    $html = $this->actingAs($mission->client)->get(route('missions.show', $mission))->assertOk()->getContent();

    expect($html)->toContain("Comment s'est passée la mission avec")
        ->toContain('<legend class="mb-1.5 text-sm font-medium text-ink">Note globale</legend>')
        ->toContain('Communication')->toContain('Qualité du travail')->toContain('Respect des délais')
        ->toContain('type="radio" id="rating-5" name="rating" value="5"')
        ->toContain('5 étoiles sur 5')
        ->toContain('Publier mon avis');

    // Ni le freelance ni un visiteur ne voient le formulaire.
    $this->actingAs($mission->freelancer)->get(route('missions.show', $mission))->assertDontSee('Publier mon avis');
});

test('le profil affiche la moyenne, la répartition, les critères et chaque avis avec sa mission', function () {
    $dev = User::factory()->withProfile()->create();
    $loyal = User::factory()->withProfile()->create(['name' => 'Cliente Fidèle']);
    $other = User::factory()->withProfile()->create(['name' => 'Autre Client']);

    foreach ([[$loyal, 5], [$loyal, 4], [$other, 3]] as [$client, $rating]) {
        $mission = completedMission($client, $dev, 180_000, 12);
        $this->actingAs($client)->post(route('missions.review', $mission), reviewData($rating));
    }

    $html = $this->get(route('devs.show', $dev))->getContent();

    expect($html)->toContain('Avis <span class="text-ink-muted">(3)</span>')
        ->toContain('>4,0<')
        ->toContain('aria-label="Répartition des notes"')
        ->toContain('style="width: 33%"')
        ->toContain('Qualité du travail')
        ->toContain(Money::budgetRange(180_000))
        ->toContain('Livrée en 12 jours')
        ->toContain('Client fidèle');

    // « Client fidèle » seulement sur les avis du client qui a confié 2 missions.
    expect(substr_count($html, 'Client fidèle'))->toBe(2);
});

test('« ★ 4,8 (6) » dans les cartes de l\'annuaire', function () {
    $dev = User::factory()->withProfile()->create();
    $mission = completedMission(dev: $dev);
    $this->actingAs($mission->client)->post(route('missions.review', $mission), reviewData(5));
    auth()->logout();

    $this->get('/devs')->assertSee('5,0', false)->assertSee('(1)', false)->assertSee('Note moyenne : 5,0 sur 5', false);
});

test('pas de note affichée sans avis', function () {
    User::factory()->withProfile()->create();

    $this->get('/devs')->assertDontSee('Note moyenne');
});

test('sur une mission terminée, pas de barre d\'action pour le client, et le « + » flottant cède la place à la barre', function () {
    $mission = completedMission();

    $this->actingAs($mission->client)->get(route('missions.show', $mission))->assertDontSee('<div data-action-bar', false);
    expect($this->get(route('missions.show', $mission))->getContent())->toContain('[body:has([data-action-bar])_&]:hidden');
});
