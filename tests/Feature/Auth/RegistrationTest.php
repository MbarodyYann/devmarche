<?php

use App\Enums\Availability;
use App\Models\User;

test('la page d\'inscription s\'affiche en français', function () {
    $this->get('/register')
        ->assertOk()
        ->assertSee('Rejoindre DevMarché')
        ->assertSee('Afrique francophone');
});

test('un visiteur peut créer un compte avec son profil', function () {
    $response = $this->post('/register', [
        'name' => 'Khady Faye',
        'email' => 'Khady@Exemple.SN',
        'country' => 'SN',
        'city' => 'Rufisque',
        'password' => 'motdepasse-solide',
        'password_confirmation' => 'motdepasse-solide',
    ]);

    $response->assertRedirect('/devs');
    $this->assertAuthenticated();

    $user = User::with('profile')->sole();
    expect($user->email)->toBe('khady@exemple.sn')
        ->and($user->reputation)->toBe(0)
        ->and($user->is_admin)->toBeFalse()
        ->and($user->profile->country)->toBe('SN')
        ->and($user->profile->city)->toBe('Rufisque')
        ->and($user->profile->availability)->toBe(Availability::Available);
});

test('l\'inscription refuse un pays inconnu et affiche les erreurs en français', function () {
    $this->from('/register')->post('/register', [
        'name' => 'Khady Faye',
        'email' => 'khady@exemple.sn',
        'country' => 'XX',
        'city' => '',
        'password' => 'court',
        'password_confirmation' => 'autre',
    ])
        ->assertRedirect('/register')
        ->assertSessionHasErrors([
            'country' => 'La valeur choisie pour pays est invalide.',
            'city' => 'Le champ ville est obligatoire.',
        ])
        ->assertSessionHasErrors('password');

    $this->assertGuest();
    expect(User::count())->toBe(0);
});

test('on ne peut pas créer deux comptes avec la même adresse e-mail', function () {
    User::factory()->withProfile()->create(['email' => 'awa@exemple.sn']);

    $this->post('/register', [
        'name' => 'Awa Bis',
        'email' => 'AWA@exemple.sn',
        'country' => 'SN',
        'city' => 'Dakar',
        'password' => 'motdepasse-solide',
        'password_confirmation' => 'motdepasse-solide',
    ])->assertSessionHasErrors(['email' => 'Un compte existe déjà avec cette adresse e-mail.']);

    expect(User::count())->toBe(1);
});
