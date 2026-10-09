<?php

use App\Enums\ApplicationStatus;
use App\Enums\MissionStatus;
use App\Models\Application;
use App\Models\Mission;
use App\Models\User;

beforeEach(function () {
    $this->client = User::factory()->withProfile()->create(['name' => 'Aïssatou Diallo']);
    $this->dev = User::factory()->withProfile()->create(['name' => 'Moussa Ndiaye']);
    $this->other = User::factory()->withProfile()->create(['name' => 'Fatou Sow']);
    $this->mission = Mission::factory()->for($this->client, 'client')->create(['budget_fcfa' => 150000]);
});

function apply(User $user, Mission $mission, array $data = [])
{
    return test()->actingAs($user)->post(route('missions.apply', $mission), array_merge([
        'message' => 'Bonjour, j\'ai déjà intégré Wave deux fois cette année, je peux démarrer lundi.',
        'proposed_fcfa' => '140 000',
    ], $data));
}

test('le cycle complet : postuler, retenir, terminer, et +20 cauris', function () {
    apply($this->dev, $this->mission)->assertRedirect(route('missions.show', $this->mission));
    apply($this->other, $this->mission);

    $mine = Application::where('user_id', $this->dev->id)->sole();
    expect($mine->status)->toBe(ApplicationStatus::Pending)->and($mine->proposed_fcfa)->toBe(140000);

    // Le client voit les candidatures et retient Moussa.
    $this->actingAs($this->client)->get(route('missions.show', $this->mission))
        ->assertSee('Candidatures')->assertSee('Moussa Ndiaye')->assertSee('Retenir');

    $this->actingAs($this->client)->post(route('missions.accept', [$this->mission, $mine]))
        ->assertRedirect(route('missions.show', $this->mission));

    $this->mission->refresh();
    expect($this->mission->status)->toBe(MissionStatus::Assigned)
        ->and($this->mission->freelancer_id)->toBe($this->dev->id)
        ->and($this->mission->assigned_at)->not->toBeNull()
        ->and($mine->fresh()->status)->toBe(ApplicationStatus::Accepted)
        ->and(Application::where('user_id', $this->other->id)->sole()->status)->toBe(ApplicationStatus::Rejected);

    // Le client clôture la mission.
    $this->actingAs($this->client)->post(route('missions.complete', $this->mission))
        ->assertSessionHas('status');

    expect($this->mission->fresh()->status)->toBe(MissionStatus::Completed)
        ->and($this->mission->fresh()->completed_at)->not->toBeNull()
        ->and($this->dev->fresh()->reputation)->toBe(20)
        ->and($this->dev->reputationEvents()->sole())
        ->reason->toBe('mission_completed')
        ->points->toBe(20)
        ->source_id->toBe($this->mission->id);

    // La mission apparaît dans les missions réalisées du dev.
    $this->get(route('devs.show', $this->dev))->assertSee($this->mission->title);
});

test('on ne peut pas postuler à sa propre mission', function () {
    apply($this->client, $this->mission)->assertForbidden();
    expect(Application::count())->toBe(0);
});

test('on ne peut pas postuler deux fois', function () {
    apply($this->dev, $this->mission);
    apply($this->dev, $this->mission)->assertForbidden();

    expect(Application::count())->toBe(1);
});

test('on ne peut pas postuler à une mission qui n\'est plus ouverte', function () {
    $this->mission->forceFill(['status' => MissionStatus::Assigned])->save();

    apply($this->dev, $this->mission)->assertForbidden();
});

test('un visiteur ne peut pas postuler', function () {
    $this->post(route('missions.apply', $this->mission), ['message' => str_repeat('a', 30), 'proposed_fcfa' => 1000])
        ->assertRedirect('/login');
});

test('la candidature valide le message et le prix', function () {
    apply($this->dev, $this->mission, ['message' => 'Dispo', 'proposed_fcfa' => 'gratuit'])
        ->assertSessionHasErrors(['message', 'proposed_fcfa']);
});

test('seul le client peut retenir un candidat', function () {
    apply($this->dev, $this->mission);
    $application = Application::sole();

    $this->actingAs($this->other)->post(route('missions.accept', [$this->mission, $application]))->assertForbidden();
    $this->actingAs($this->dev)->post(route('missions.accept', [$this->mission, $application]))->assertForbidden();

    expect($this->mission->fresh()->status)->toBe(MissionStatus::Open);
});

test('on ne peut pas retenir la candidature d\'une autre mission', function () {
    $otherMission = Mission::factory()->create();
    $foreign = Application::factory()->for($otherMission)->create();

    $this->actingAs($this->client)->post(route('missions.accept', [$this->mission, $foreign]))->assertForbidden();
});

test('on ne peut pas retenir un second candidat', function () {
    apply($this->dev, $this->mission);
    apply($this->other, $this->mission);
    [$first, $second] = Application::orderBy('id')->get();

    $this->actingAs($this->client)->post(route('missions.accept', [$this->mission, $first]));
    $this->actingAs($this->client)->post(route('missions.accept', [$this->mission, $second]))->assertForbidden();

    expect($this->mission->fresh()->freelancer_id)->toBe($this->dev->id);
});

test('on ne peut pas terminer une mission non attribuée', function () {
    $this->actingAs($this->client)->post(route('missions.complete', $this->mission))->assertForbidden();

    expect($this->mission->fresh()->status)->toBe(MissionStatus::Open);
});

test('seul le client peut terminer la mission', function () {
    $mission = Mission::factory()->for($this->client, 'client')->assignedTo($this->dev)->create();

    $this->actingAs($this->dev)->post(route('missions.complete', $mission))->assertForbidden();

    expect($this->dev->fresh()->reputation)->toBe(0);
});

test('le client peut annuler une mission ouverte ; les candidatures en attente sont refusées', function () {
    apply($this->dev, $this->mission);

    $this->actingAs($this->client)->post(route('missions.cancel', $this->mission))->assertSessionHas('status');

    expect($this->mission->fresh()->status)->toBe(MissionStatus::Cancelled)
        ->and(Application::sole()->status)->toBe(ApplicationStatus::Rejected);
});

test('on ne peut pas annuler une mission terminée', function () {
    $this->mission->forceFill(['status' => MissionStatus::Completed])->save();

    $this->actingAs($this->client)->post(route('missions.cancel', $this->mission))->assertForbidden();

    expect($this->mission->fresh()->status)->toBe(MissionStatus::Completed);
});

test('seul le client peut annuler', function () {
    $this->actingAs($this->dev)->post(route('missions.cancel', $this->mission))->assertForbidden();
});

test('le candidat voit sa candidature et les autres ne voient pas les candidatures', function () {
    apply($this->dev, $this->mission);

    $this->actingAs($this->dev)->get(route('missions.show', $this->mission))
        ->assertSee('Votre candidature')
        ->assertSee('Vous avez postulé le')
        ->assertDontSee('Retenir');

    $this->actingAs($this->other)->get(route('missions.show', $this->mission))
        ->assertDontSee('Bonjour, j&#039;ai déjà intégré Wave', false)
        ->assertSee('Postuler');
});

test('les candidatures sont limitées à 30 par heure', function () {
    $missions = Mission::factory()->count(31)->create();

    foreach ($missions->take(30) as $mission) {
        apply($this->dev, $mission);
    }

    apply($this->dev, $missions->last())->assertStatus(429);
});
