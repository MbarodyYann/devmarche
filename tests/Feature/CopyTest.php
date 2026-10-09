<?php

use App\Models\Mission;
use App\Models\User;
use App\Support\Copy;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DevSeeder;

/*
| Textes de 11-redaction.md, présents mot pour mot sur chaque page.
*/

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->wave = Mission::where('title', 'like', '%API de paiement Wave%')->sole();
    $this->moussa = User::where('email', DevSeeder::DEMO_DEV_EMAIL)->sole();
    $this->aissatou = User::where('email', DevSeeder::DEMO_STARTUP_EMAIL)->sole();
});

test('missions : titre, mécanisme et CTA visiteur', function () {
    $this->get('/missions')
        ->assertSee('Votre prochain client <em class="font-serif italic text-secondary-400">est déjà ici.</em>', false)
        ->assertSee('Filtrez par compétence et par pays, postulez avec votre prix en 2 minutes : le client compare les profils et vous paie en Mobile Money.')
        ->assertSee(Copy::SIGNUP_CTA)
        ->assertSee(Copy::RECRUIT_LINK);
});

test('annuaire : titre, mécanisme et CTA visiteur', function () {
    $this->get('/devs')
        ->assertSee('Le bon dev est peut-être <em class="font-serif italic text-secondary-400">dans votre ville.</em>', false)
        ->assertSee('Filtrez par compétence, pays et disponibilité : les cauris montrent qui a déjà livré des missions et aidé la communauté.')
        ->assertSee(Copy::SIGNUP_CTA);
});

test('fiche mission, visiteur : décrocher la mission avec le CTA unique', function () {
    $this->get(route('missions.show', $this->wave))
        ->assertSee('Décrochez cette mission')
        ->assertSee(Copy::SIGNUP_CTA)
        ->assertSee('Déjà membre ? Se connecter');
});

test('fiche mission, membre : postuler, avec la réassurance', function () {
    $this->actingAs($this->moussa)->get(route('missions.show', $this->wave))
        ->assertSee('Un message et votre prix : le client voit votre profil, vos compétences et vos cauris.')
        ->assertSee('Postuler à cette mission')
        ->assertSee('Gratuit · Une seule candidature par mission, soignez-la.');
});

test('fiche mission, client : choisir son dev, retenir, annuler en lien discret', function () {
    $this->actingAs($this->aissatou)->get(route('missions.show', $this->wave))
        ->assertSee('Choisissez votre dev')
        ->assertSee('Retenir ce dev')
        ->assertSee('Annuler la mission');
});

test('fiche mission, client d\'une mission en cours : confirmer la fin', function () {
    $mission = Mission::where('status', 'assigned')->with('client')->first();

    $this->actingAs($mission->client)->get(route('missions.show', $mission))
        ->assertSee('Confirmer la fin de la mission')
        ->assertSee('Le dev reçoit +20 cauris de réputation.');
});

test('publier : titre, mécanisme, bouton et réassurance', function () {
    $this->actingAs($this->aissatou)->get(route('missions.create'))
        ->assertSee('Décrivez votre besoin, les devs viennent à vous.')
        ->assertSee('Publiez gratuitement : les devs postulent avec leur prix, vous comparez leurs profils et leurs cauris, puis vous choisissez.')
        ->assertSee('Publier ma mission')
        ->assertSee('Gratuit · Aucun paiement avant d\'avoir choisi votre dev.', false)
        ->assertSee('Montant total en FCFA, sans espace ni point : 150000');
});

test('inscription : le bouton reprend le CTA unique', function () {
    $this->get('/register')
        ->assertSee('Votre profil en ligne en 2 minutes.')
        ->assertSee('Nom, e-mail, pays et ville : c&#039;est tout. Vous ajouterez vos compétences ensuite.', false)
        ->assertSee(Copy::SIGNUP_CTA)
        ->assertSee('Déjà membre ? Se connecter');
});

test('connexion et 404', function () {
    $this->get('/login')->assertSee('Content de vous revoir.')->assertSee('Pas encore membre ? '.Copy::SIGNUP_CTA);
    $this->get('/introuvable')->assertNotFound()->assertSee('Retour à l\'accueil', false)->assertSee('Voir les missions');
});

test('profil du propriétaire : bienvenue, compétences et première mission', function () {
    $nouveau = User::factory()->withProfile()->create();

    $this->actingAs($nouveau)->withSession(['welcome' => true])->get(route('devs.show', $nouveau))
        ->assertSee('Bienvenue !')
        ->assertSee('Compléter mon profil')
        ->assertSee('Votre première mission terminée apparaîtra ici, avec +20 cauris.')
        ->assertSee('Pas encore de cauris : chaque mission terminée et chaque réponse utile en rapportent.');

    // Sans le message de bienvenue (session neuve), l'encart parle des compétences manquantes.
    $autre = User::factory()->withProfile()->create();
    $this->flushSession();
    $this->actingAs($autre)->get(route('devs.show', $autre))
        ->assertSee('Les clients filtrent d\'abord par compétence.', false);
});

test('le libellé du CTA visiteur est identique partout et jamais « Créer un compte »', function () {
    foreach (['/', '/missions', '/devs', '/register', route('missions.show', $this->wave)] as $url) {
        $html = $this->get($url)->getContent();

        expect($html)->toContain(Copy::SIGNUP_CTA)
            ->not->toContain('Créer un compte')
            ->not->toContain('Créer mon compte');
    }
});

test('aucun point ni signe décoratif devant un texte sur les pages principales', function () {
    $pages = ['/', '/missions', '/devs', route('devs.show', $this->moussa), route('missions.show', $this->wave)];

    foreach ($pages as $url) {
        $html = $this->get($url)->getContent();

        expect($html)->not->toContain('size-1.5 rounded-full')
            ->not->toContain('●')
            ->not->toContain('h-px w-8');
    }
});

test('en-tête compact : réassurance sous le CTA visiteur, sur les 3 pages', function (string $url) {
    $this->get($url)->assertSeeInOrder([Copy::SIGNUP_CTA, 'data-reassurance', Copy::REASSURANCE_RISK], false);
})->with(['/missions', '/questions', '/devs']);

test('en-tête compact : pas de réassurance d\'inscription pour un membre', function () {
    $this->actingAs($this->moussa)->get('/missions')->assertDontSee('data-reassurance', false);
});
