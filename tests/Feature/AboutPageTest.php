<?php

use App\Support\Credits;

/*
| Page « À propos » : projet, équipe, stack, licences (lues dans les fichiers de verrouillage), usage de l'IA.
*/

test('la page présente le projet, l\'équipe, la stack, les licences et l\'usage de l\'IA', function () {
    $this->get('/a-propos')->assertOk()->assertSeeInOrder([
        'Le projet',
        'L\'équipe Baobab Stack',
        'Yaya Niang', 'Représentant de l\'équipe',
        'Aminata Sall',
        'La stack',
        'Licences',
        'Usage de l\'intelligence artificielle',
        'Claude Code (Anthropic)',
        'ChatGPT (OpenAI)', 'image de l\'accueil.',
    ])->assertSee('CADEV 2026')->assertSee('Systalink');
});

test('le pied de page mène à la page « À propos »', function () {
    $this->get('/')->assertSee('href="'.route('about').'"', false);
});

test('le tableau des licences reprend les versions installées (composer.lock, package-lock.json)', function () {
    $lock = json_decode(file_get_contents(base_path('composer.lock')), true);
    $laravel = collect($lock['packages'])->firstWhere('name', 'laravel/framework');
    $npm = json_decode(file_get_contents(base_path('package-lock.json')), true);

    $this->get('/a-propos')
        ->assertSee(ltrim($laravel['version'], 'v'))
        ->assertSee($npm['packages']['node_modules/tailwindcss']['version'])
        ->assertSee('SIL OFL 1.1')
        ->assertSee('ISC');
});

test('toutes les dépendances directes (Composer et npm) sont déclarées', function () {
    $declared = collect(config('credits.packages'))->pluck('package');
    $composer = json_decode(file_get_contents(base_path('composer.json')), true);
    $npm = json_decode(file_get_contents(base_path('package.json')), true);

    $direct = collect(array_keys($composer['require'] + $composer['require-dev']))
        ->reject(fn (string $name) => $name === 'php' || str_starts_with($name, 'ext-'))
        ->merge(array_keys(($npm['dependencies'] ?? []) + ($npm['devDependencies'] ?? [])));

    expect($direct->diff($declared)->values()->all())->toBe([]);
});

test('aucune licence interdite (GPL, AGPL, LGPL) sans alternative permissive', function () {
    foreach (Credits::licenses() as $row) {
        $options = explode(' ou ', $row['license']);
        $permissive = collect($options)->reject(fn (string $l) => preg_match('/GPL/i', $l));

        expect($permissive)->not->toBeEmpty("{$row['name']} : {$row['license']}")
            ->and($row['version'])->not->toBe('—', "{$row['name']} introuvable dans les fichiers de verrouillage");
    }
});

test('mention légale des marques citées (aucune affiliation, paiement simulé)', function () {
    $this->get('/a-propos')->assertSee('Wave, Orange Money et MTN MoMo sont des marques de leurs propriétaires respectifs. DevMarché n\'est affilié à aucune de ces sociétés ; le paiement est simulé dans cette version.', false);
});
