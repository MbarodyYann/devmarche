<?php

use App\Models\User;

test('la page de connexion s\'affiche', function () {
    $this->get('/login')->assertOk()->assertSee('Se connecter');
});

test('un membre peut se connecter avec ses identifiants', function () {
    $user = User::factory()->withProfile()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect('/devs');

    $this->assertAuthenticatedAs($user);
});

test('un mauvais mot de passe est refusé avec un message en français', function () {
    $user = User::factory()->withProfile()->create();

    $this->from('/login')
        ->post('/login', ['email' => $user->email, 'password' => 'mauvais'])
        ->assertRedirect('/login')
        ->assertSessionHasErrors(['email' => 'Ces identifiants ne correspondent à aucun compte.']);

    $this->assertGuest();
});

test('la connexion est limitée après 5 tentatives', function () {
    $user = User::factory()->withProfile()->create();

    foreach (range(1, 5) as $attempt) {
        $this->post('/login', ['email' => $user->email, 'password' => 'mauvais']);
    }

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertStatus(429);

    $this->assertGuest();
});

test('un membre peut se déconnecter', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)->post('/logout')->assertRedirect('/');

    $this->assertGuest();
});
