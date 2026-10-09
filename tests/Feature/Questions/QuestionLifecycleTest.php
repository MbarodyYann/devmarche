<?php

use App\Livewire\Questions\Create;
use App\Livewire\Vote;
use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Services\ReputationService;
use Livewire\Livewire;

beforeEach(function () {
    $this->asker = User::factory()->withProfile()->create(['name' => 'Mariama Ba']);
    $this->expert = User::factory()->withProfile()->create(['name' => 'Rodrigue Hounsou']);
    $this->voter = User::factory()->withProfile()->create(['name' => 'Kossi Mensah']);
});

test('le cycle complet : poser, répondre, voter, accepter', function () {
    // Poser une question (Livewire, avec tags).
    Livewire::actingAs($this->asker)->test(Create::class)
        ->set('title', 'Comment vérifier la signature d\'un webhook Wave ?')
        ->set('body', "Je reçois les webhooks mais je ne sais pas les vérifier.\n\n```php\n\$sig = \$request->header('Wave-Signature');\n```")
        ->set('tags', 'Wave API, Laravel')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('questions.show', Question::sole()));

    $question = Question::with('skills')->sole();
    expect($question->skills->pluck('name')->all())->toBe(['Laravel', 'Wave API'])
        ->and($question->body_html)->toContain('hljs')->toContain('Copier');

    // Répondre.
    $this->actingAs($this->expert)->post(route('answers.store', $question), [
        'body' => 'Calculez un HMAC-SHA256 du corps brut avec votre secret et comparez avec hash_equals.',
    ])->assertSessionHas('status');

    $answer = Answer::sole();
    expect($question->fresh()->answers_count)->toBe(1);

    // Voter (+10 cauris pour l'auteur de la réponse).
    Livewire::actingAs($this->voter)->test(Vote::class, ['votable' => $answer])->call('vote', 1)->assertSet('myVote', 1);
    expect($answer->fresh()->score)->toBe(1)->and($this->expert->fresh()->reputation)->toBe(10);

    // Accepter (+15).
    $this->actingAs($this->asker)->post(route('answers.accept', [$question, $answer]))->assertSessionHas('status');
    expect($question->fresh()->accepted_answer_id)->toBe($answer->id)
        ->and($this->expert->fresh()->reputation)->toBe(25);

    // La page affiche la réponse acceptée en premier.
    $this->get(route('questions.show', $question))
        ->assertOk()
        ->assertSee('Acceptée')
        ->assertSee('Résolue');
});

test('revoter dans le même sens annule le vote et l\'événement de réputation', function () {
    $answer = Answer::factory()->for($this->expert)->create();

    $component = Livewire::actingAs($this->voter)->test(Vote::class, ['votable' => $answer]);
    $component->call('vote', 1)->assertSet('myVote', 1);
    $component->call('vote', 1)->assertSet('myVote', 0);

    expect($answer->fresh()->score)->toBe(0)
        ->and($this->expert->fresh()->reputation)->toBe(0)
        ->and($this->expert->reputationEvents()->pluck('reason')->all())->toBe(['answer_upvoted', 'answer_upvote_cancelled']);
});

test('inverser un vote annule l\'ancien effet puis applique le nouveau', function () {
    $this->voter->forceFill(['reputation' => 60])->save();
    $answer = Answer::factory()->for($this->expert)->create();

    $component = Livewire::actingAs($this->voter)->test(Vote::class, ['votable' => $answer]);
    $component->call('vote', 1);
    $component->call('vote', -1)->assertSet('myVote', -1);

    expect($answer->fresh()->score)->toBe(-1)
        ->and($this->expert->fresh()->reputation)->toBe(-2);
});

test('on ne vote pas pour son propre contenu', function () {
    $answer = Answer::factory()->for($this->expert)->create();

    Livewire::actingAs($this->expert)->test(Vote::class, ['votable' => $answer])
        ->call('vote', 1)
        ->assertSet('myVote', 0)
        ->assertSet('message', 'Vous ne pouvez pas voter pour votre propre contenu.');

    expect($answer->fresh()->score)->toBe(0);
});

test('le vote négatif demande 50 cauris', function () {
    $answer = Answer::factory()->for($this->expert)->create();

    Livewire::actingAs($this->voter)->test(Vote::class, ['votable' => $answer])
        ->assertSee('Le vote négatif est ouvert à partir de 50 cauris')
        ->call('vote', -1)
        ->assertSet('myVote', 0);

    expect($answer->fresh()->score)->toBe(0);

    $this->voter->forceFill(['reputation' => 50])->save();
    Livewire::actingAs($this->voter->fresh())->test(Vote::class, ['votable' => $answer])->call('vote', -1)->assertSet('myVote', -1);
});

test('un visiteur qui vote est envoyé vers la connexion', function () {
    $answer = Answer::factory()->create();

    Livewire::test(Vote::class, ['votable' => $answer])->call('vote', 1)->assertRedirect(route('login'));
});

test('les votes sur une question ne rapportent pas de cauris', function () {
    $question = Question::factory()->for($this->asker)->create();

    Livewire::actingAs($this->voter)->test(Vote::class, ['votable' => $question])->call('vote', 1);

    expect($question->fresh()->score)->toBe(1)->and($this->asker->fresh()->reputation)->toBe(0);
});

test('changer de réponse acceptée retire 15 cauris à l\'ancienne', function () {
    $question = Question::factory()->for($this->asker)->create();
    $first = Answer::factory()->for($question)->for($this->expert)->create();
    $second = Answer::factory()->for($question)->for($this->voter)->create();

    $this->actingAs($this->asker)->post(route('answers.accept', [$question, $first]));
    $this->actingAs($this->asker)->post(route('answers.accept', [$question, $second]));

    expect($question->fresh()->accepted_answer_id)->toBe($second->id)
        ->and($this->expert->fresh()->reputation)->toBe(0)
        ->and($this->voter->fresh()->reputation)->toBe(15);

    // Re-cliquer sur l'acceptée la retire.
    $this->actingAs($this->asker)->post(route('answers.accept', [$question, $second]));
    expect($question->fresh()->accepted_answer_id)->toBeNull()->and($this->voter->fresh()->reputation)->toBe(0);
});

test('seul l\'auteur de la question accepte, et seulement une réponse de sa question', function () {
    $question = Question::factory()->for($this->asker)->create();
    $answer = Answer::factory()->for($question)->for($this->expert)->create();
    $foreign = Answer::factory()->create();

    $this->actingAs($this->voter)->post(route('answers.accept', [$question, $answer]))->assertForbidden();
    $this->actingAs($this->asker)->post(route('answers.accept', [$question, $foreign]))->assertForbidden();

    expect($question->fresh()->accepted_answer_id)->toBeNull();
});

test('accepter sa propre réponse ne rapporte rien', function () {
    $question = Question::factory()->for($this->asker)->create();
    $answer = Answer::factory()->for($question)->for($this->asker)->create();

    $this->actingAs($this->asker)->post(route('answers.accept', [$question, $answer]));

    expect($this->asker->fresh()->reputation)->toBe(0);
});

test('un visiteur ne peut ni poser ni répondre', function () {
    $question = Question::factory()->create();

    $this->get(route('questions.create'))->assertRedirect('/login');
    $this->post(route('answers.store', $question), ['body' => str_repeat('a', 30)])->assertRedirect('/login');
});

test('la question et la réponse sont validées', function () {
    Livewire::actingAs($this->asker)->test(Create::class)
        ->set('title', 'Aide')
        ->set('body', 'Court')
        ->set('tags', 'a, b, c, d, e, f')
        ->call('save')
        ->assertHasErrors(['title', 'body']);

    Livewire::actingAs($this->asker)->test(Create::class)
        ->set('title', 'Une question parfaitement claire et précise')
        ->set('body', str_repeat('Détail utile. ', 5))
        ->set('tags', 'a, b, c, d, e, f')
        ->call('save')
        ->assertHasErrors(['tags']);

    $question = Question::factory()->create();
    $this->actingAs($this->expert)->post(route('answers.store', $question), ['body' => 'Oui.'])->assertSessionHasErrors('body');
});

test('les questions sont limitées à 10 par heure', function () {
    foreach (range(1, 10) as $i) {
        Livewire::actingAs($this->asker)->test(Create::class)
            ->set('title', "Question numéro $i parfaitement claire")
            ->set('body', str_repeat('Détail utile. ', 5))
            ->call('save');
    }

    Livewire::actingAs($this->asker)->test(Create::class)
        ->set('title', 'Encore une question parfaitement claire')
        ->set('body', str_repeat('Détail utile. ', 5))
        ->call('save')
        ->assertHasErrors('title');

    expect(Question::count())->toBe(10);
});

test('la réputation reste égale à la somme des événements après des votes croisés', function () {
    $answers = Answer::factory()->count(3)->for($this->expert)->create();
    app(ReputationService::class)->award($this->voter, 100, 'test'); // pour pouvoir voter contre

    foreach ($answers as $answer) {
        $c = Livewire::actingAs($this->voter)->test(Vote::class, ['votable' => $answer]);
        $c->call('vote', 1);
        $c->call('vote', -1);
    }

    $this->artisan('reputation:recalculate')->expectsOutputToContain('cohérentes');
    expect($this->expert->fresh()->reputation)->toBe(-6);
});
