<?php

use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Models\Question;
use App\Models\User;
use App\Support\Seo;

/*
| Partage (WhatsApp, copie du lien), balises Open Graph et Twitter par page, SEO de base.
*/

dataset('fiches', [
    'mission' => fn () => [route('missions.show', $m = Mission::first()), $m->title],
    'question' => fn () => [route('questions.show', $q = Question::first()), $q->title],
    'profil' => fn () => [route('devs.show', $u = User::whereHas('profile')->first()), $u->name],
]);

test('bouton « Partager sur WhatsApp » avec le titre et l\'URL encodés, et « Copier le lien »', function (array $page) {
    [$url, $title] = $page;
    $html = $this->get($url)->assertOk()->getContent();

    preg_match('/href="(https:\/\/wa\.me\/\?text=[^"]+)"/', $html, $match);
    $text = rawurldecode(substr(html_entity_decode($match[1] ?? ''), strlen('https://wa.me/?text=')));

    expect($match)->not->toBeEmpty()
        ->and($text)->toContain($title)->toContain($url)
        ->and($html)->toContain('Partager sur WhatsApp')
        ->toContain('rel="noopener noreferrer"')
        ->toContain('Copier le lien')
        ->toContain("'Lien copié'");
})->with('fiches');

test('chaque fiche a ses balises Open Graph, Twitter, sa description et son URL canonique', function (array $page) {
    [$url, $title] = $page;
    $html = $this->get($url)->getContent();

    expect($html)->toContain('<meta property="og:title" content="'.e($title).' · DevMarché">')
        ->toContain('<meta name="twitter:title" content="'.e($title).' · DevMarché">')
        ->toContain('<meta property="og:url" content="'.$url.'">')
        ->toContain('<link rel="canonical" href="'.$url.'">')
        ->toContain('<meta property="og:image" content="'.asset('images/og-default.png').'">')
        ->toContain('<meta name="twitter:image" content="'.asset('images/og-default.png').'">');

    preg_match('/<meta name="description" content="([^"]*)">/', $html, $description);
    preg_match('/<meta property="og:description" content="([^"]*)">/', $html, $og);
    expect(mb_strlen(html_entity_decode($description[1])))->toBeLessThanOrEqual(160)->toBeGreaterThan(30)
        ->and($og[1])->toBe($description[1])
        ->and($description[1])->not->toBe(e(Seo::DEFAULT_DESCRIPTION));
})->with('fiches');

test('la description d\'une mission est un extrait de sa description, sans balises', function () {
    $mission = Mission::first();
    $html = $this->get(route('missions.show', $mission))->getContent();

    expect($html)->toContain('<meta name="description" content="'.e(Seo::excerpt($mission->description)).'">');
});

test('meta description propre à chaque page publique', function (string $url) {
    preg_match('/<meta name="description" content="([^"]*)">/', $this->get($url)->getContent(), $match);

    expect($match[1] ?? '')->not->toBe('');
})->with(['/', '/missions', '/questions', '/devs', '/a-propos', '/login', '/register']);

test('les listes ont pour description le sous-titre de leur en-tête', function () {
    $this->get('/missions')->assertSee('<meta name="description" content="Filtrez par compétence et par pays', false);
});

test('URL canonique sans les paramètres de filtre', function () {
    $this->get('/missions?pays=SN&tri=budget')->assertSee('<link rel="canonical" href="'.route('missions.index').'">', false);
});

test('Seo::excerpt coupe à 160 caractères au plus', function () {
    expect(mb_strlen(Seo::excerpt(str_repeat('Mobile Money ', 40))))->toBe(160)
        ->and(Seo::excerpt("<p>Court\n texte</p>"))->toBe('Court texte')
        ->and(Seo::excerpt(''))->toBe(Seo::DEFAULT_DESCRIPTION);
});

test('les pages privées et la 404 ne sont pas indexées', function () {
    $this->get('/page-inexistante')->assertNotFound()->assertSee('<meta name="robots" content="noindex">', false);
    $this->actingAs(User::first())->get(route('missions.create'))->assertSee('<meta name="robots" content="noindex">', false);
    $this->get('/missions')->assertDontSee('noindex');
});

test('/sitemap.xml liste l\'accueil, les listes, les missions ouvertes, les questions et les profils', function () {
    $response = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    $xml = simplexml_load_string($response->getContent());
    $locs = collect();
    foreach ($xml->url as $url) {
        $locs->push((string) $url->loc);
    }

    expect($locs)->toContain(route('home'), route('missions.index'), route('questions.index'), route('devs.index'), route('about'));

    Mission::where('status', MissionStatus::Open)->each(fn ($m) => expect($locs)->toContain(route('missions.show', $m)));
    Mission::where('status', '!=', MissionStatus::Open)->each(fn ($m) => expect($locs)->not->toContain(route('missions.show', $m)));
    Question::all()->each(fn ($q) => expect($locs)->toContain(route('questions.show', $q)));
    User::whereHas('profile')->each(fn ($u) => expect($locs)->toContain(route('devs.show', $u)));
});

test('/robots.txt pointe vers le plan du site et exclut les formulaires privés', function () {
    $this->get('/robots.txt')->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('Sitemap: '.route('sitemap'))
        ->assertSee('Disallow: /demo/')
        ->assertSee('Disallow: /missions/publier');
});
