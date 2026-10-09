<?php

use App\Enums\Availability;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function validProfileData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Moussa Ndiaye',
        'headline' => 'Dev Laravel & Wave API',
        'bio' => 'Développeur back-end basé à Thiès.',
        'country' => 'SN',
        'city' => 'Thiès',
        'availability' => 'busy',
        'skills' => 'Laravel, Wave API, laravel ,  Orange Money',
        'github_url' => 'https://github.com/moussa',
        'portfolio_url' => 'https://moussa.dev',
    ], $overrides);
}

test('le profil public est visible par tous', function () {
    $dev = User::factory()->withProfile(['city' => 'Thiès', 'country' => 'SN', 'headline' => 'Dev Laravel'])->create(['name' => 'Moussa Ndiaye']);
    $dev->skills()->attach(Skill::create(['name' => 'Wave API']));

    $this->get(route('devs.show', $dev))
        ->assertOk()
        ->assertSee('Moussa Ndiaye')
        ->assertSee('Dev Laravel')
        ->assertSee('Thiès · Sénégal')
        ->assertSee('Wave API')
        ->assertDontSee('Modifier mon profil');
});

test('un identifiant non numérique renvoie une 404', function () {
    $this->get('/devs/abc')->assertNotFound();
});

test('le propriétaire voit le bouton de modification sur son profil', function () {
    $dev = User::factory()->withProfile()->create();

    $this->actingAs($dev)->get(route('devs.show', $dev))->assertSee('Modifier mon profil');
});

test('un visiteur est redirigé vers la connexion pour modifier un profil', function () {
    $dev = User::factory()->withProfile()->create();

    $this->get(route('profile.edit', $dev))->assertRedirect('/login');
    $this->put(route('profile.update', $dev), validProfileData())->assertRedirect('/login');
});

test('un membre peut modifier son propre profil', function () {
    $dev = User::factory()->withProfile()->create();

    $this->actingAs($dev)->get(route('profile.edit', $dev))->assertOk()->assertSee('Modifier mon profil');

    $this->actingAs($dev)
        ->put(route('profile.update', $dev), validProfileData())
        ->assertRedirect(route('devs.show', $dev))
        ->assertSessionHas('status');

    $dev->refresh()->load(['profile', 'skills']);

    expect($dev->name)->toBe('Moussa Ndiaye')
        ->and($dev->profile->city)->toBe('Thiès')
        ->and($dev->profile->availability)->toBe(Availability::Busy)
        ->and($dev->profile->github_url)->toBe('https://github.com/moussa')
        ->and($dev->skills->pluck('name')->all())->toBe(['Laravel', 'Orange Money', 'Wave API']);
});

test('les compétences existantes sont réutilisées par leur slug', function () {
    $existing = Skill::create(['name' => 'Wave API']);
    $dev = User::factory()->withProfile()->create();

    $this->actingAs($dev)->put(route('profile.update', $dev), validProfileData(['skills' => 'wave api']));

    expect(Skill::count())->toBe(1)
        ->and($dev->skills()->sole()->id)->toBe($existing->id);
});

test('un membre ne peut pas afficher le formulaire du profil d\'un autre', function () {
    [$moussa, $fatou] = User::factory()->withProfile()->count(2)->create();

    $this->actingAs($moussa)->get(route('profile.edit', $fatou))->assertForbidden();
});

test('un membre ne peut pas modifier le profil d\'un autre', function () {
    [$moussa, $fatou] = User::factory()->withProfile(['city' => 'Dakar'])->count(2)->create();

    $this->actingAs($moussa)
        ->put(route('profile.update', $fatou), validProfileData(['city' => 'Piraterie']))
        ->assertForbidden();

    expect($fatou->profile()->value('city'))->toBe('Dakar');
});

test('la modification du profil valide les champs côté serveur', function (array $data, string $field) {
    $dev = User::factory()->withProfile()->create();

    $this->actingAs($dev)
        ->put(route('profile.update', $dev), validProfileData($data))
        ->assertSessionHasErrors($field);
})->with([
    'pays inconnu' => [['country' => 'ZZ'], 'country'],
    'ville vide' => [['city' => ''], 'city'],
    'disponibilité inconnue' => [['availability' => 'en_vacances'], 'availability'],
    'GitHub hors github.com' => [['github_url' => 'https://evil.example/moussa'], 'github_url'],
    'portfolio javascript:' => [['portfolio_url' => 'javascript:alert(1)'], 'portfolio_url'],
    'trop de compétences' => [['skills' => implode(',', range('a', 'm'))], 'skills'],
    'compétence trop longue' => [['skills' => str_repeat('x', 41)], 'skills'],
]);

test('l\'avatar est redimensionné en WebP de 256 px et l\'ancien est supprimé', function () {
    Storage::fake('public');
    $dev = User::factory()->withProfile(['avatar_path' => 'avatars/ancien.webp'])->create();
    Storage::disk('public')->put('avatars/ancien.webp', 'ancien');

    $this->actingAs($dev)
        ->put(route('profile.update', $dev), validProfileData([
            'avatar' => UploadedFile::fake()->image('photo.jpg', 900, 600),
        ]))
        ->assertSessionHasNoErrors();

    $path = $dev->profile()->value('avatar_path');
    expect($path)->toStartWith('avatars/')->toEndWith('.webp');
    Storage::disk('public')->assertExists($path);
    Storage::disk('public')->assertMissing('avatars/ancien.webp');

    [$width, $height, $type] = getimagesizefromstring(Storage::disk('public')->get($path));
    expect([$width, $height, $type])->toBe([256, 256, IMAGETYPE_WEBP]);
});

test('un fichier qui n\'est pas une image est refusé comme avatar', function () {
    Storage::fake('public');
    $dev = User::factory()->withProfile()->create();

    $this->actingAs($dev)
        ->put(route('profile.update', $dev), validProfileData([
            'avatar' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]))
        ->assertSessionHasErrors('avatar');
});
