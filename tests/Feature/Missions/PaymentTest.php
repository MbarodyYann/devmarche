<?php

use App\Enums\ApplicationStatus;
use App\Enums\MissionStatus;
use App\Enums\PaymentStatus;
use App\Models\Mission;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\MissionCompleted;
use App\Services\MissionService;
use App\Support\Money;
use Illuminate\Support\Facades\Http;

/*
| Paiement Mobile Money SIMULÉ avec séquestre (lot 6, point 3).
*/

beforeEach(function () {
    Http::preventStrayRequests(); // rien n'est envoyé à un service externe
    $this->client = User::factory()->withProfile()->create();
    $this->dev = User::factory()->withProfile()->create(['name' => 'Moussa Dev']);
    $this->mission = Mission::factory()->for($this->client, 'client')->create();
    $this->application = app(MissionService::class)->apply($this->mission, $this->dev, ['message' => 'Disponible', 'proposed_fcfa' => 150_000, 'delivery_days' => 10]);
});

function secureFunds($test, ?string $code = null)
{
    $test->actingAs($test->client)->post(route('payments.store', [$test->mission, $test->application]), ['provider' => 'wave', 'phone' => '70 000 00 45']);

    return $test->actingAs($test->client)->post(route('payments.verify', [$test->mission, $test->application]), [
        'code' => $code ?? session('payments.pending.'.$test->application->id)['code'],
    ]);
}

test('« Retenir ce dev » mène à la sécurisation des fonds, avec le bandeau de simulation', function () {
    $this->actingAs($this->client)->get(route('missions.show', $this->mission))
        ->assertSee(route('payments.create', [$this->mission, $this->application]), false);

    $this->actingAs($this->client)->get(route('payments.create', [$this->mission, $this->application]))->assertOk()
        ->assertSee("Simulation : aucun argent réel n'est débité.", false)
        ->assertSee('Wave')->assertSee('Orange Money')->assertSee('Free Money')->assertSee('MTN MoMo')->assertSee('Moov Money')
        ->assertSee('150')
        ->assertSee('Recevoir le code de confirmation');
});

test('le code de confirmation simulé s\'affiche, avec le numéro masqué', function () {
    $this->actingAs($this->client)->post(route('payments.store', [$this->mission, $this->application]), ['provider' => 'orange_money', 'phone' => '77 123 45 67'])
        ->assertRedirect(route('payments.confirm', [$this->mission, $this->application]));

    $code = session('payments.pending.'.$this->application->id)['code'];
    expect($code)->toMatch('/^\d{4}$/');

    $this->actingAs($this->client)->get(route('payments.confirm', [$this->mission, $this->application]))->assertOk()
        ->assertSee("SMS simulé d'Orange Money au 77 ••• •• 67")
        ->assertSee($code)
        ->assertDontSee('77 123 45 67');
});

test('bon code : fonds sécurisés, dev retenu, référence DM-XXXXXX ; mauvais code : refusé', function () {
    $this->actingAs($this->client)->post(route('payments.store', [$this->mission, $this->application]), ['provider' => 'wave', 'phone' => '70 000 00 45']);
    $wrong = session('payments.pending.'.$this->application->id)['code'] === '0000' ? '1111' : '0000';
    $this->actingAs($this->client)->post(route('payments.verify', [$this->mission, $this->application]), ['code' => $wrong])
        ->assertSessionHasErrors('code');
    expect(Payment::count())->toBe(0);

    secureFunds($this)->assertRedirect(route('missions.show', $this->mission))->assertSessionHas('status');

    $payment = Payment::sole();
    expect($payment->status)->toBe(PaymentStatus::Held)
        ->and($payment->amount_fcfa)->toBe(150_000)
        ->and($payment->provider)->toBe('wave')
        ->and($payment->reference)->toMatch('/^DM-[A-Z0-9]{6}$/')
        ->and($payment->maskedPhone())->toBe('70 ••• •• 45')
        ->and($this->mission->fresh()->status)->toBe(MissionStatus::Assigned)
        ->and($this->application->fresh()->status)->toBe(ApplicationStatus::Accepted);
});

test('« Fonds sécurisés » pour tous, détail pour le client et le dev seulement', function () {
    secureFunds($this);

    auth()->logout();
    $this->get(route('missions.show', $this->mission))->assertSee('Fonds sécurisés')->assertDontSee(Payment::sole()->reference);
    $this->actingAs($this->dev)->get(route('missions.show', $this->mission))->assertSee(Payment::sole()->reference)->assertSee('70 ••• •• 45');
    $this->actingAs(User::factory()->create())->get(route('missions.show', $this->mission))->assertDontSee(Payment::sole()->reference);
});

test('à la clôture, « Paiement libéré au dev » ; à l\'annulation, « Remboursé »', function () {
    secureFunds($this);
    $this->actingAs($this->client)->post(route('missions.complete', $this->mission));
    expect(Payment::sole()->status)->toBe(PaymentStatus::Released);
    $this->get(route('missions.show', $this->mission))->assertSee('Paiement libéré au dev');
    expect($this->dev->fresh()->notifications()->where('type', MissionCompleted::class)->sole()->data['text'])->toContain(Money::fcfa(150_000).' FCFA libérés (paiement simulé)');

    $other = Mission::factory()->for($this->client, 'client')->create();
    $application = app(MissionService::class)->apply($other, $this->dev, ['message' => 'Dispo', 'proposed_fcfa' => 80_000, 'delivery_days' => 5]);
    $this->actingAs($this->client)->post(route('payments.store', [$other, $application]), ['provider' => 'mtn_momo', 'phone' => '70 000 00 46']);
    $this->actingAs($this->client)->post(route('payments.verify', [$other, $application]), ['code' => session('payments.pending.'.$application->id)['code']]);
    $this->actingAs($this->client)->post(route('missions.cancel', $other));

    expect($other->payment()->sole()->status)->toBe(PaymentStatus::Refunded);
});

test('validation : opérateur de la liste et numéro d\'au moins 8 chiffres', function () {
    $this->actingAs($this->client)->post(route('payments.store', [$this->mission, $this->application]), ['provider' => 'paypal', 'phone' => '12'])
        ->assertSessionHasErrors(['provider', 'phone']);
});

test('seul le client de la mission ouverte peut sécuriser les fonds', function () {
    $this->actingAs($this->dev)->get(route('payments.create', [$this->mission, $this->application]))->assertForbidden();
    $this->actingAs(User::factory()->create())->post(route('payments.store', [$this->mission, $this->application]), ['provider' => 'wave', 'phone' => '70 000 00 45'])->assertForbidden();
});

test('la bande de garanties de l\'accueil annonce les fonds sécurisés, en précisant la simulation', function () {
    $this->get('/')->assertSee('Fonds sécurisés jusqu&#039;à la livraison', false)->assertSee('simulé dans cette version');
});

test('Z1 : sur une mission attribuée, plus d\'aide au choix ni de délai moyen', function () {
    $this->actingAs($this->client)->get(route('missions.show', $this->mission))
        ->assertSee('Choisissez votre dev')->assertSee('Délai moyen proposé')->assertSee('Comparez les prix');

    secureFunds($this);

    $this->actingAs($this->client)->get(route('missions.show', $this->mission))
        ->assertSee('Candidatures')->assertDontSee('Choisissez votre dev')
        ->assertDontSee('Délai moyen proposé')->assertDontSee('Comparez les prix');
});
