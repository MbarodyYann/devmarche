<?php

use App\Livewire\Questions\Index;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Skill;
use App\Models\User;
use App\Support\Copy;
use Livewire\Livewire;

beforeEach(function () {
    $wave = Skill::create(['name' => 'Wave API']);
    $flutter = Skill::create(['name' => 'Flutter']);

    $this->webhook = Question::factory()->create(['title' => 'Vérifier la signature d\'un webhook Wave', 'body' => 'Le serveur reçoit des notifications de paiement.', 'created_at' => now()->subDays(3)]);
    $this->webhook->skills()->attach($wave);

    $this->offline = Question::factory()->create(['title' => 'Flutter hors connexion', 'body' => 'Synchroniser les données quand le réseau revient.', 'score' => 5, 'created_at' => now()->subDay()]);
    $this->offline->skills()->attach($flutter);

    $this->ussd = Question::factory()->create(['title' => 'Sessions USSD', 'body' => 'Garder l\'état entre les écrans du menu.']);

    $answer = Answer::factory()->for($this->offline)->create();
    $this->offline->forceFill(['answers_count' => 1, 'accepted_answer_id' => $answer->id])->save();
});

test('la page des questions affiche les textes de 11 et les questions récentes en premier', function () {
    $this->get('/questions')
        ->assertOk()
        ->assertSee('Votre question ne se perdra plus <em class="font-serif italic text-secondary-400">dans un groupe WhatsApp.</em>', false)
        ->assertSee('Posez-la en français avec les bons tags : la communauté répond et vote, et la meilleure réponse reste trouvable par tous.')
        ->assertSeeInOrder(['Sessions USSD', 'Flutter hors connexion', 'Vérifier la signature']);
});

test('un membre voit « Poser ma question », un visiteur le CTA unique', function () {
    $this->get('/questions')->assertSee(Copy::SIGNUP_CTA)->assertDontSee('Poser ma question');
    $this->actingAs(User::factory()->withProfile()->create())->get('/questions')->assertSee('Poser ma question');
});

test('la recherche plein texte trouve les mots du corps, en français (racines)', function () {
    // « notification » trouve « notifications » ; « synchronisation » trouve « Synchroniser ».
    Livewire::test(Index::class)->set('q', 'notification')
        ->assertSee('Vérifier la signature')->assertDontSee('Sessions USSD');

    Livewire::test(Index::class)->set('q', 'écran')
        ->assertSee('Sessions USSD')->assertDontSee('Flutter hors connexion');
});

test('filtre par tag', function () {
    Livewire::test(Index::class)->set('tag', 'flutter')
        ->assertSee('Flutter hors connexion')->assertDontSee('Sessions USSD');
});

test('filtre par statut : sans réponse et résolues', function () {
    Livewire::test(Index::class)->set('status', 'sans-reponse')
        ->assertSee('Sessions USSD')->assertDontSee('Flutter hors connexion');

    Livewire::test(Index::class)->set('status', 'resolues')
        ->assertSee('Flutter hors connexion')->assertDontSee('Sessions USSD');
});

test('tri par votes', function () {
    $this->get('/questions?tri=votes')->assertSeeInOrder(['Flutter hors connexion', 'Sessions USSD']);
});

test('une question résolue porte l\'étiquette Résolue, sans point', function () {
    $html = $this->get('/questions')->getContent();

    expect($html)->toContain('Résolue')->toContain('Sans réponse')->not->toContain('size-1.5 rounded-full');
});

test('état vide quand rien ne correspond', function () {
    Livewire::test(Index::class)->set('q', 'kubernetes')->assertSee('Personne n&#039;a encore posé cette question.', false);
});
