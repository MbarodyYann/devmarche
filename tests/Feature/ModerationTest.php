<?php

use App\Livewire\Missions\Index as MissionIndex;
use App\Livewire\Questions\Index as QuestionIndex;
use App\Models\Answer;
use App\Models\Mission;
use App\Models\Question;
use App\Models\Report;
use App\Models\User;
use App\Services\AnswerService;
use App\Services\PulseService;
use App\Support\Demo;
use Livewire\Livewire;

/*
| Signalement et modération (lot 6, point 6).
*/

beforeEach(function () {
    $this->author = User::factory()->withProfile()->create();
    $this->member = User::factory()->withProfile()->create();
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->question = app(AnswerService::class)->ask($this->author, 'Comment déployer Laravel sur un VPS ?', 'Je cherche une méthode simple et sûre.', ['Laravel']);
});

function reportContent($test, User $user, string $type, int $id, string $reason = 'spam')
{
    return $test->actingAs($user)->from('/questions')->post(route('reports.store'), ['type' => $type, 'id' => $id, 'reason' => $reason]);
}

test('un membre signale un contenu, une seule fois', function () {
    reportContent($this, $this->member, 'question', $this->question->id, 'off_topic')
        ->assertRedirect('/questions')->assertSessionHas('status', 'Merci, c\'est signalé. La modération va vérifier ce contenu.');
    reportContent($this, $this->member, 'question', $this->question->id)
        ->assertSessionHas('status', 'Vous avez déjà signalé ce contenu. La modération va le vérifier.');

    expect(Report::count())->toBe(1)
        ->and(Report::sole()->reason->label())->toBe('Hors sujet');
});

test('on ne signale pas son propre contenu ; type et raison sont vérifiés ; un visiteur doit se connecter', function () {
    reportContent($this, $this->author, 'question', $this->question->id)->assertForbidden();
    reportContent($this, $this->member, 'user', $this->author->id)->assertSessionHasErrors('type');
    reportContent($this, $this->member, 'question', $this->question->id, 'boring')->assertSessionHasErrors('reason');

    auth()->logout();
    $this->post(route('reports.store'), ['type' => 'question', 'id' => $this->question->id, 'reason' => 'spam'])->assertRedirect(route('login'));
    expect(Report::count())->toBe(0);
});

test('le bouton « Signaler » est discret, réservé aux membres, jamais sur son propre contenu', function () {
    $answer = app(AnswerService::class)->answer($this->member, $this->question, 'Utilisez Forge ou un script de déploiement.');

    $html = $this->actingAs($this->member)->get(route('questions.show', $this->question))->getContent();
    expect(substr_count($html, 'data-report'))->toBe(1) // la question (pas sa propre réponse)
        ->and($html)->toContain('Pourquoi signalez-vous ce contenu ?')->toContain('Propos offensants');

    auth()->logout();
    $this->get(route('questions.show', $this->question))->assertDontSee('data-report', false);
});

test('la file de modération est réservée aux admins et liste les contenus signalés', function () {
    reportContent($this, $this->member, 'question', $this->question->id, 'spam');
    reportContent($this, User::factory()->withProfile()->create(), 'question', $this->question->id, 'spam');

    $this->actingAs($this->member)->get(route('moderation.index'))->assertForbidden();
    $this->actingAs($this->admin)->get(route('moderation.index'))->assertOk()
        ->assertSee('Comment déployer Laravel sur un VPS ?')
        ->assertSee('2 signalements')
        ->assertSee('Spam ou publicité × 2')
        ->assertSee('Masquer');
});

test('masquer rend le contenu invisible du public ; rétablir le fait revenir', function () {
    $mission = Mission::factory()->create();
    reportContent($this, $this->member, 'mission', $mission->id);

    $this->actingAs($this->admin)->post(route('moderation.hide', ['mission', $mission->id]))->assertRedirect();
    $this->actingAs($this->admin)->post(route('moderation.hide', ['question', $this->question->id]));

    auth()->logout();
    $this->get(route('missions.show', $mission))->assertNotFound();
    $this->get(route('questions.show', $this->question))->assertNotFound();
    Livewire::withQueryParams(['statut' => 'tous'])->test(MissionIndex::class)->assertDontSee($mission->title);
    Livewire::test(QuestionIndex::class)->assertDontSee($this->question->title);
    expect(app(PulseService::class)->devsByCountry())->toBeArray()
        ->and(Mission::count())->toBe(0);

    // La modération le voit toujours, marqué « Masqué », et peut le rétablir.
    $this->actingAs($this->admin)->get(route('moderation.index'))->assertSee('Masqué')->assertSee('Rétablir');
    $this->actingAs($this->admin)->post(route('moderation.restore', ['mission', $mission->id]));
    auth()->logout();
    $this->get(route('missions.show', $mission))->assertOk();
});

test('une réponse masquée disparaît de sa question', function () {
    $answer = app(AnswerService::class)->answer($this->member, $this->question, 'Réponse hors sujet à masquer.');
    $this->actingAs($this->admin)->post(route('moderation.hide', ['answer', $answer->id]));

    auth()->logout();
    $this->get(route('questions.show', $this->question))->assertOk()->assertDontSee('Réponse hors sujet à masquer.');
    expect(Answer::withoutGlobalScope('visible')->find($answer->id)->isHidden())->toBeTrue();
});

test('connexion démo du modérateur : il arrive sur la file de modération', function () {
    config(['devmarche.demo' => true]);
    User::factory()->create(['email' => Demo::ADMIN_EMAIL, 'is_admin' => true]);

    $this->get('/login')->assertSee('Entrer comme modérateur');
    $this->post('/demo/moderation')->assertRedirect(route('moderation.index'));
});

test('élision dans la file : « Réponse d\'Edem », « Réponse de Moussa »', function () {
    $edem = User::factory()->withProfile()->create(['name' => 'Edem Afenyo']);
    $moussa = User::factory()->withProfile()->create(['name' => 'Moussa Ndiaye']);
    foreach ([$edem, $moussa] as $author) {
        $answer = app(AnswerService::class)->answer($author, $this->question, 'Une réponse à vérifier.');
        reportContent($this, $this->member, 'answer', $answer->id);
    }

    $this->actingAs($this->admin)->get(route('moderation.index'))
        ->assertSee("Réponse d'Edem Afenyo")
        ->assertSee('Réponse de Moussa Ndiaye');
});

test('les extraits de la file ne montrent pas la syntaxe Markdown', function () {
    $question = app(AnswerService::class)->ask($this->author, 'Images en 404 après déploiement', 'Le dossier `public` renvoie une **404** sur le serveur.', ['Laravel']);
    reportContent($this, $this->member, 'question', $question->id);

    $this->actingAs($this->admin)->get(route('moderation.index'))
        ->assertSee('Le dossier public renvoie une 404 sur le serveur.')
        ->assertDontSee('`public`', false)
        ->assertDontSee('**404**', false);
});
