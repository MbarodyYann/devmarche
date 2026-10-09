<?php

use App\Models\Application;
use App\Models\Mission;
use App\Models\User;
use App\Notifications\AnswerAccepted;
use App\Notifications\AnswerPosted;
use App\Notifications\ApplicationDecided;
use App\Notifications\ApplicationReceived;
use App\Notifications\MissionCompleted;
use App\Notifications\ReviewReceived;
use App\Services\AnswerService;
use App\Services\MissionService;
use App\Services\ReviewService;
use Illuminate\Support\Collection;

/*
| Notifications dans l'application (lot 6, point 2).
*/

function notificationsOf(User $user, string $class): Collection
{
    return $user->fresh()->notifications()->where('type', $class)->get();
}

test('candidature reçue : le client est prévenu', function () {
    $mission = Mission::factory()->create();
    $dev = User::factory()->withProfile()->create(['name' => 'Awa Dev']);

    $this->actingAs($dev)->post(route('missions.apply', $mission), ['message' => 'Bonjour, je suis disponible tout de suite.', 'proposed_fcfa' => 120000, 'delivery_days' => 10]);

    $notification = notificationsOf($mission->client, ApplicationReceived::class)->sole();
    expect($notification->data['text'])->toContain('Awa Dev a postulé à « '.$mission->title.' »')->toContain('120')
        ->and($notification->data['url'])->toBe(route('missions.show', $mission).'#candidatures');
});

test('retenir un dev : il est prévenu, les autres candidats aussi', function () {
    $mission = Mission::factory()->create();
    $chosen = User::factory()->withProfile()->create();
    $others = User::factory()->withProfile()->count(2)->create();
    $services = app(MissionService::class);

    $application = $services->apply($mission, $chosen, ['message' => 'Choisi', 'proposed_fcfa' => 100000, 'delivery_days' => 7]);
    foreach ($others as $other) {
        $services->apply($mission, $other, ['message' => 'Autre', 'proposed_fcfa' => 90000, 'delivery_days' => 8]);
    }

    $services->accept($mission, $application);

    expect(notificationsOf($chosen, ApplicationDecided::class)->sole()->data['text'])->toContain('vous êtes retenu');
    foreach ($others as $other) {
        expect(notificationsOf($other, ApplicationDecided::class)->sole()->data['text'])->toContain('n\'a pas été retenue');
    }
});

test('mission annulée : les candidats en attente sont prévenus', function () {
    $mission = Mission::factory()->create();
    $dev = User::factory()->withProfile()->create();
    app(MissionService::class)->apply($mission, $dev, ['message' => 'Dispo', 'proposed_fcfa' => 50000, 'delivery_days' => 5]);

    app(MissionService::class)->cancel($mission);

    expect(notificationsOf($dev, ApplicationDecided::class)->sole()->data['text'])->toContain('a été annulée par le client');
});

test('mission terminée et avis : le dev est prévenu', function () {
    $dev = User::factory()->withProfile()->create();
    $mission = Mission::factory()->assignedTo($dev)->create();

    app(MissionService::class)->complete($mission->fresh());
    expect(notificationsOf($dev, MissionCompleted::class)->sole()->data['text'])->toContain('terminée : +20 cauris');

    app(ReviewService::class)->submit($mission->fresh(), $mission->client, ['rating' => 5, 'communication' => 5, 'quality' => 5, 'timeliness' => 4, 'comment' => null]);
    $review = notificationsOf($dev, ReviewReceived::class)->sole();
    expect($review->data['text'])->toContain('vous a laissé un avis 5★')
        ->and($review->data['url'])->toBe(route('devs.show', $dev).'#avis');
});

test('nouvelle réponse et réponse acceptée : prévenus, sauf pour soi-même', function () {
    $author = User::factory()->withProfile()->create();
    $helper = User::factory()->withProfile()->create(['name' => 'Kofi Aide']);
    $answers = app(AnswerService::class);
    $question = $answers->ask($author, 'Comment vérifier un webhook Wave ?', 'Je reçois les webhooks mais je ne sais pas les vérifier.', ['Laravel']);

    $answer = $answers->answer($helper, $question, 'Avec hash_equals sur la signature HMAC du corps brut.');
    $answers->answer($author, $question->fresh(), 'Merci, je teste et je reviens.');

    expect(notificationsOf($author, AnswerPosted::class))->toHaveCount(1)
        ->and(notificationsOf($author, AnswerPosted::class)->sole()->data['text'])->toContain('Kofi Aide a répondu à votre question');

    $answers->toggleAccepted($question->fresh(), $answer);
    expect(notificationsOf($helper, AnswerAccepted::class)->sole()->data['text'])->toContain('a été acceptée : +15 cauris');
});

test('la cloche montre le compteur et les 10 dernières ; « Tout marquer comme lu » vide le compteur', function () {
    $client = User::factory()->withProfile()->create();
    $mission = Mission::factory()->for($client, 'client')->create();
    foreach (User::factory()->withProfile()->count(12)->create() as $dev) {
        app(MissionService::class)->apply($mission, $dev, ['message' => 'Dispo', 'proposed_fcfa' => 50000, 'delivery_days' => 5]);
    }

    $html = $this->actingAs($client)->get('/missions')->getContent();
    $start = strpos($html, 'data-bell');
    $bell = substr($html, $start, strpos($html, 'Voir toutes les notifications', $start) - $start);
    expect($html)->toContain('aria-label="Notifications, 12 non lues"')
        ->and(substr_count($bell, 'href="'.url('/notifications').'/'))->toBe(10)
        ->and($bell)->toContain('9+')->toContain('Tout marquer comme lu');

    $this->actingAs($client)->post(route('notifications.read-all'))->assertRedirect();
    expect($client->fresh()->unreadNotifications()->count())->toBe(0);
});

test('ouvrir une notification la marque comme lue et suit son lien ; celle d\'un autre est introuvable', function () {
    $mission = Mission::factory()->create();
    app(MissionService::class)->apply($mission, User::factory()->withProfile()->create(), ['message' => 'Dispo', 'proposed_fcfa' => 50000, 'delivery_days' => 5]);
    $notification = $mission->client->notifications()->sole();

    $this->actingAs(User::factory()->create())->get(route('notifications.open', $notification->id))->assertNotFound();
    $this->actingAs($mission->client)->get(route('notifications.open', $notification->id))
        ->assertRedirect(route('missions.show', $mission).'#candidatures');
    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('page /notifications et menu « Moi » sur mobile', function () {
    $mission = Mission::factory()->create();
    app(MissionService::class)->apply($mission, User::factory()->withProfile()->create(['name' => 'Ibou Dev']), ['message' => 'Dispo', 'proposed_fcfa' => 50000, 'delivery_days' => 5]);

    $this->actingAs($mission->client)->get(route('notifications.index'))->assertOk()
        ->assertSee('Ibou Dev a postulé')->assertSee('Tout marquer comme lu');

    $html = $this->actingAs($mission->client)->get('/missions')->getContent();
    expect($html)->toContain('data-me-menu')->toContain('Moi, 1 notification non lue')->toContain('Se déconnecter');

    $this->get(route('notifications.index'))->assertOk();
    auth()->logout();
    $this->get(route('notifications.index'))->assertRedirect(route('login'));
});

test('le texte de la carte du client dit vrai : les autres candidats sont prévenus', function () {
    $mission = Mission::factory()->create();

    $this->actingAs($mission->client)->get(route('missions.show', $mission))
        ->assertSee('Quand vous retenez un dev, les autres candidats sont prévenus.');
});
