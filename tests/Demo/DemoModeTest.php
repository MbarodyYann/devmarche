<?php

use App\Models\Mission;
use App\Models\User;
use App\Support\Demo;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\RateLimiter;

/*
| Démo pour le jury (02, « Spécial jury ») : connexion en un clic, bandeau, réinitialisation nocturne.
*/

beforeEach(function () {
    RateLimiter::clear('demo');
});

test('sans APP_DEMO : pas d\'encart, et la connexion démo renvoie 404', function () {
    config(['devmarche.demo' => false]);

    $this->get('/login')->assertOk()->assertDontSee('Démo pour le jury');
    $this->get('/register')->assertOk()->assertDontSee('Démo pour le jury');
    $this->post('/demo/moussa')->assertNotFound();
    $this->assertGuest();
});

test('avec APP_DEMO : encart sur la connexion et l\'inscription, avec les 2 personas', function (string $url) {
    config(['devmarche.demo' => true]);

    $this->get($url)->assertOk()
        ->assertSee('Démo pour le jury')
        ->assertSee('Entrer comme Aïssatou (startup, Dakar)')
        ->assertSee('Entrer comme Moussa (dev, Thiès)')
        ->assertSee('action="'.route('demo.login', 'moussa').'"', false)
        ->assertSee('name="_token"', false);
})->with(['/login', '/register']);

test('connexion comme Moussa : missions pour lui, et bandeau du mode démo', function () {
    config(['devmarche.demo' => true]);

    $this->post('/demo/moussa')->assertRedirect(route('missions.index', ['pour_moi' => 1]));
    $this->assertAuthenticatedAs(User::where('email', Demo::DEV_EMAIL)->sole());

    $this->get('/')->assertSee('Vous êtes en mode démo (Moussa). Les données sont réinitialisées chaque nuit.');
});

test('connexion comme Aïssatou : elle arrive sur sa mission ouverte avec des candidats', function () {
    config(['devmarche.demo' => true]);
    $aissatou = User::where('email', Demo::STARTUP_EMAIL)->sole();
    $mission = Mission::where('client_id', $aissatou->id)->where('status', 'open')->has('applications')->latest()->first();

    $this->post('/demo/aissatou')->assertRedirect(route('missions.show', $mission));
    $this->assertAuthenticatedAs($aissatou);
    $this->get(route('missions.show', $mission))->assertSee('Vous êtes en mode démo (Aïssatou)');
});

test('persona inconnu refusé', function () {
    config(['devmarche.demo' => true]);

    $this->post('/demo/admin')->assertNotFound();
    $this->post('/demo/123')->assertNotFound();
    $this->assertGuest();
});

test('la connexion démo est protégée par CSRF (POST dans le groupe web) et limitée', function () {
    // Laravel désactive la vérification CSRF pendant les tests : on vérifie la route elle-même.
    $route = app('router')->getRoutes()->getByName('demo.login');

    expect($route->methods())->toBe(['POST'])
        ->and($route->gatherMiddleware())->toContain('web', 'guest', 'throttle:demo');
});

test('la connexion démo est limitée à 10 par minute', function () {
    config(['devmarche.demo' => true]);

    foreach (range(1, 10) as $i) {
        $this->post('/demo/moussa')->assertRedirect();
        auth()->logout();
    }

    $this->post('/demo/moussa')->assertStatus(429);
});

test('pas de bandeau pour un membre ordinaire, ni hors mode démo', function () {
    config(['devmarche.demo' => true]);
    $this->actingAs(User::where('email', '!=', Demo::DEV_EMAIL)->where('email', '!=', Demo::STARTUP_EMAIL)->first())
        ->get('/')->assertDontSee('data-demo-banner', false);

    config(['devmarche.demo' => false]);
    $this->actingAs(User::where('email', Demo::DEV_EMAIL)->sole())->get('/')->assertDontSee('data-demo-banner', false);
});

test('demo:reset est refusée hors APP_DEMO', function () {
    config(['devmarche.demo' => false]);

    $this->artisan('demo:reset')->assertFailed();
    expect(User::count())->toBeGreaterThan(0);
});

test('demo:reset est planifiée chaque nuit à 3 h', function () {
    $event = collect(app(Schedule::class)->events())->first(fn ($e) => str_contains($e->command, 'demo:reset'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 3 * * *');
});
