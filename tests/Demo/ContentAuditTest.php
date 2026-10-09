<?php

use App\Enums\MissionStatus;
use App\Livewire\Missions\Index;
use App\Models\Answer;
use App\Models\Application;
use App\Models\Mission;
use App\Models\Question;
use App\Models\User;
use App\Support\Copy;
use App\Support\Demo;

/*
| Audit du contenu (11 : « est-ce que c'est bien ? », « est-ce que ça a sa place ici ? », on ne se justifie jamais).
*/

dataset('pages publiques', [
    'accueil' => fn () => '/',
    'missions' => fn () => '/missions',
    'questions' => fn () => '/questions',
    'annuaire' => fn () => '/devs',
    'fiche mission' => fn () => route('missions.show', Mission::first()),
    'fiche question' => fn () => route('questions.show', Question::first()),
    'profil' => fn () => route('devs.show', User::orderByDesc('reputation')->first()),
    'à propos' => fn () => '/a-propos',
    'connexion' => fn () => '/login',
    'inscription' => fn () => '/register',
]);

test('aucune justification : ni règlement, ni jury, ni « exige » (mode démo actif compris)', function (string $url) {
    config(['devmarche.demo' => true]);
    $text = strip_tags($this->get($url)->assertOk()->getContent());

    expect(mb_strtolower($text))->not->toContain('règlement')->not->toContain('jury')->not->toContain('exige');

    // « concours » : seulement la ligne factuelle de l'équipe, sur À propos.
    if ($url !== '/a-propos') {
        expect(mb_strtolower($text))->not->toContain('concours');
    }
})->with('pages publiques');

test('À propos : pas de CTA, liste IA sans justification, licences sans phrase d\'introduction', function () {
    $page = $this->get('/a-propos')->getContent();
    $html = substr($page, strpos($page, '<main'), strpos($page, '</main>') - strpos($page, '<main'));

    expect($html)->not->toContain(Copy::SIGNUP_CTA)
        ->not->toContain(Copy::RECRUIT_LINK)
        ->toContain('Équipe du Sénégal, au concours CADEV 2026 organisé par Systalink.')
        ->toContain('Claude Code (Anthropic)</strong> : conception, documentation, code, tests et revue de code.')
        ->toContain('d&#039;images IA</strong> : image d&#039;illustration de l&#039;accueil.')
        ->toContain('Filtres instantanés, votes, aperçu Markdown<')
        ->not->toContain('relit le code')
        ->not->toContain('validée par')
        ->not->toContain('Uniquement des licences')
        ->not->toContain('GPL');
});

test('accueil : titre de page avec la promesse', function () {
    $this->get('/')->assertSee('<title>DevMarché · Missions en FCFA pour les devs d&#039;Afrique francophone</title>', false);
});

test('fiche mission : le client montre son activité, pas de cauris à 0, et sa carte est masquée pour lui', function () {
    $aissatou = User::where('email', Demo::STARTUP_EMAIL)->sole();
    $mission = Mission::where('client_id', $aissatou->id)->where('status', MissionStatus::Open)->first();
    $count = Mission::where('client_id', $aissatou->id)->count();

    $html = $this->get(route('missions.show', $mission))->getContent();
    expect($html)->toContain('Publiée par')
        ->toContain($count.' missions publiées')
        ->toContain('Membre depuis '.$aissatou->created_at->translatedFormat('M Y'));

    $this->actingAs($aissatou)->get(route('missions.show', $mission))->assertDontSee('Publiée par');
});

test('les cauris ne s\'affichent que pour un membre qui en a', function () {
    $zero = User::where('reputation', 0)->whereHas('questions')->first();
    $question = $zero->questions()->first();

    $html = $this->get(route('questions.show', $question))->getContent();
    $author = substr($html, strpos($html, 'href="'.route('devs.show', $zero).'"'), 900);

    expect($author)->not->toContain('title="Réputation');
});

test('le partage vient après le contenu principal, pas en tête de page', function () {
    $mission = Mission::first();
    $html = $this->get(route('missions.show', $mission))->getContent();
    expect(strpos($html, 'data-share'))->toBeGreaterThan(strpos($html, '<h1'));

    $question = Question::first();
    $html = $this->get(route('questions.show', $question))->getContent();
    expect(strpos($html, 'data-share'))->toBeGreaterThan(strpos($html, 'prose-dm'));
});

test('messages des candidatures : variés et sans gabarit automatique', function () {
    $messages = Application::pluck('message');

    expect($messages->filter(fn ($m) => str_contains($m, 'Compétences utiles ici'))->count())->toBe(0)
        ->and($messages->unique()->count())->toBeGreaterThanOrEqual(12);
});

test('profil : pas de surtitre, « N votes » sur les réponses, missions réalisées « Pour client »', function () {
    $jm = User::where('name', 'like', 'Jean-Marc%')->sole();
    $answer = Answer::where('user_id', $jm->id)->orderByDesc('score')->first();
    $completed = Mission::where('freelancer_id', $jm->id)->where('status', MissionStatus::Completed)->with('client')->latest('completed_at')->first();

    $html = $this->get(route('devs.show', $jm))->getContent();

    expect($html)->not->toContain('Profil de dev')
        ->toContain($answer->score.' votes')
        ->toContain('Pour '.e($completed->client->name))
        ->toContain('Terminée le '.$completed->completed_at->translatedFormat('j M Y'));

    $section = substr($html, strpos($html, 'data-completed-mission'), 1500);
    expect($section)->not->toContain('candidature');
});

test('édition du profil : pas de surtitre, bouton « Enregistrer mon profil »', function () {
    $moussa = User::where('email', Demo::DEV_EMAIL)->sole();

    $this->actingAs($moussa)->get(route('profile.edit', $moussa))
        ->assertDontSee('Votre vitrine')
        ->assertSee('Enregistrer mon profil');
});

test('les langues commencent par une majuscule, accents compris (Éwé)', function () {
    $this->get('/devs')->assertSee('>Éwé<', false)->assertDontSee('>éwé<', false);
});

test('fiche question en visiteur : pas de lien recruteur', function () {
    $this->get(route('questions.show', Question::first()))->assertDontSee(Copy::RECRUIT_LINK);
});

test('plus de surtitres qui répètent le titre', function () {
    $this->get('/register')->assertDontSee('<p class="eyebrow mb-3">Inscription</p>', false);
    $this->get('/login')->assertDontSee('<p class="eyebrow mb-3">Connexion</p>', false);
    $this->get('/page-inexistante')->assertDontSee('Erreur 404');
    $this->actingAs(User::first())->get(route('missions.create'))
        ->assertDontSee('<p class="eyebrow mb-3">Publier une mission</p>', false)
        ->assertSee('Montant total en FCFA. Ex. 150 000')
        ->assertDontSee('sans espace ni point');
});

test('pied de page : accroche une seule fois, « À propos » seul, et © 2026 DevMarché', function () {
    $html = $this->get('/')->getContent();
    $footer = substr($html, strpos($html, '<footer'));

    expect(substr_count($footer, "Par et pour les devs d'Afrique francophone."))->toBe(0)
        ->and($footer)->toContain('>À propos</a>')
        ->not->toContain('#equipe')
        ->not->toContain('#licences')
        ->toContain('© 2026 DevMarché');
});

test('recherche par mots-clés sur /missions (plein texte en français, ?q=)', function () {
    $wave = Mission::where('title', 'like', '%API de paiement Wave%')->sole();

    Livewire\Livewire::withQueryParams(['q' => 'paiements Wave', 'statut' => 'tous'])
        ->test(Index::class)
        ->assertSee($wave->title)
        ->tap(fn ($c) => expect($c->instance()->missions->total())->toBeLessThan(Mission::count()));

    Livewire\Livewire::withQueryParams(['q' => 'Wave'])->test(Index::class)->assertSet('q', 'Wave');
});
