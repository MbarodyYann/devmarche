<?php

use App\Http\Middleware\TouchLastSeen;
use App\Livewire\Devs\Directory;
use App\Models\User;
use Livewire\Livewire;

test('un membre choisit ses langues ; elles s\'affichent « Parle français, wolof »', function () {
    $dev = User::factory()->withProfile(['country' => 'SN', 'city' => 'Dakar'])->create(['name' => 'Fatou Sow']);

    $this->actingAs($dev)->put(route('profile.update', $dev), [
        'name' => 'Fatou Sow', 'country' => 'SN', 'city' => 'Dakar', 'availability' => 'available',
        'languages' => ['wo', 'fr'],
    ])->assertSessionHasNoErrors();

    expect($dev->profile()->first()->languages)->toBe(['fr', 'wo']);
    $this->get(route('devs.show', $dev))->assertSee('Parle français, wolof');
    $this->get('/devs')->assertSee('Parle français, wolof');
});

test('une langue inconnue est refusée', function () {
    $dev = User::factory()->withProfile()->create();

    $this->actingAs($dev)->put(route('profile.update', $dev), [
        'name' => 'X', 'country' => 'SN', 'city' => 'Dakar', 'availability' => 'available', 'languages' => ['klingon'],
    ])->assertSessionHasErrors('languages.0');
});

test('l\'annuaire filtre par langue parlée', function () {
    User::factory()->withProfile(['languages' => ['fr', 'bm']])->create(['name' => 'Bakary Bambara']);
    User::factory()->withProfile(['languages' => ['fr', 'fon']])->create(['name' => 'Grâce Fon']);

    Livewire::test(Directory::class)->set('language', 'bm')
        ->assertSee('Bakary Bambara')->assertDontSee('Grâce Fon');
});

test('la dernière activité est mise à jour au plus toutes les 5 minutes', function () {
    $dev = User::factory()->withProfile()->create(['last_seen_at' => null]);

    $this->actingAs($dev)->get('/devs');
    $first = $dev->fresh()->last_seen_at;
    expect($first)->not->toBeNull();

    $this->travel(2)->minutes();
    $this->actingAs($dev->fresh())->get('/devs');
    expect($dev->fresh()->last_seen_at->equalTo($first))->toBeTrue();

    $this->travel(TouchLastSeen::INTERVAL_MINUTES + 1)->minutes();
    $this->actingAs($dev->fresh())->get('/devs');
    expect($dev->fresh()->last_seen_at->gt($first))->toBeTrue();
});

test('libellés de dernière activité', function (?int $daysAgo, ?string $label) {
    $user = new User(['name' => 'X']);
    $user->last_seen_at = $daysAgo === null ? null : now()->subDays($daysAgo);

    expect($user->activityLabel())->toBe($label);
})->with([
    [0, 'Actif aujourd\'hui'],
    [1, 'Actif hier'],
    [3, 'Actif il y a 3 j'],
    [30, 'Actif il y a 30 j'],
    [31, null],
    [null, null],
]);

test('le filtre « Actifs cette semaine »', function () {
    User::factory()->withProfile()->create(['name' => 'Actif Récent', 'last_seen_at' => now()->subDays(2)]);
    User::factory()->withProfile()->create(['name' => 'Ancien Absent', 'last_seen_at' => now()->subDays(12)]);

    Livewire::test(Directory::class)->set('activeWeek', true)
        ->assertSee('Actif Récent')->assertDontSee('Ancien Absent');
});
