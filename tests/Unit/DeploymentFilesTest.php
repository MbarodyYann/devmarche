<?php

/*
| Fichiers de déploiement (lot 5) : garde-fous sur les réglages qui ne doivent jamais régresser.
*/

function deployFile(string $path): string
{
    return file_get_contents(dirname(__DIR__, 2).'/'.$path);
}

test('le modèle .env de production désactive le débogage et limite les journaux', function () {
    $env = deployFile('.env.example.production');

    expect($env)->toContain("APP_ENV=production\n")
        ->toContain("APP_DEBUG=false\n")
        ->toContain("LOG_LEVEL=warning\n")
        ->toContain("SESSION_SECURE_COOKIE=true\n")
        ->toMatch('/^APP_KEY=$/m')
        ->toMatch('/^DB_PASSWORD=$/m');
});

test('deploy.sh s\'arrête à la première erreur et fait toutes les étapes', function () {
    $script = deployFile('deploy/deploy.sh');

    expect($script)->toStartWith('#!/usr/bin/env bash')
        ->toContain('set -euo pipefail')
        ->toContain('git pull --ff-only')
        ->toContain('composer install --no-dev --optimize-autoloader')
        ->toContain('npm ci')
        ->toContain('npm run build')
        ->toContain('artisan migrate --force')
        ->toContain('artisan config:cache')
        ->toContain('artisan route:cache')
        ->toContain('artisan view:cache')
        ->toContain('artisan event:cache')
        ->toContain('systemctl reload')
        ->and(is_executable(dirname(__DIR__, 2).'/deploy/deploy.sh'))->toBeTrue();
});

test('Nginx : en-têtes de sécurité partout, cache long des fichiers fingerprintés, PHP limité à index.php', function () {
    $site = deployFile('deploy/nginx/devmarche.conf');
    $headers = deployFile('deploy/nginx/devmarche-security-headers.conf');

    foreach (['Strict-Transport-Security', 'X-Frame-Options', 'Referrer-Policy', 'Permissions-Policy', 'Content-Security-Policy', 'X-Content-Type-Options'] as $header) {
        expect($headers)->toContain('add_header '.$header);
    }

    // Chaque bloc qui ajoute ses propres en-têtes réinclut les en-têtes de sécurité.
    expect(substr_count($site, 'include snippets/devmarche-security-headers.conf'))->toBe(substr_count($site, 'add_header Cache-Control') + 1)
        ->and($site)->toContain('max-age=31536000, immutable')
        ->toContain('gzip on;')
        ->toContain('location ~ ^/index\.php(/|$)')
        ->toContain('location ~ \.php$ { return 404; }')
        ->toContain('return 301 https://');
});

test('le planificateur tourne chaque minute sous l\'utilisateur dédié', function () {
    expect(deployFile('deploy/systemd/devmarche-scheduler.service'))->toContain('User=devmarche')->toContain('artisan schedule:run')
        ->and(deployFile('deploy/systemd/devmarche-scheduler.timer'))->toContain('OnCalendar=*-*-* *:*:00');
});
