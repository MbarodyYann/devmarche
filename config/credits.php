<?php

/*
| Page « À propos » : équipe, stack, licences et usage de l'IA (article 6 du règlement CADEV).
|
| Licences : pour chaque paquet Composer ou npm, la version et la licence sont lues dans composer.lock
| et package-lock.json (App\Support\Credits) ; on ne les recopie pas à la main. Les autres composants
| (polices, icônes, base de données) sont décrits ici. Le registre de documentation/06 reprend ce tableau.
*/

return [
    'team' => [
        ['name' => 'Yaya Niang', 'role' => 'Représentant de l\'équipe'],
        ['name' => 'Aminata Sall', 'role' => 'Membre de l\'équipe'],
    ],

    'stack' => [
        ['name' => 'Laravel 12', 'role' => 'Framework PHP (PHP 8.2)'],
        ['name' => 'Livewire 3 et Alpine.js', 'role' => 'Filtres instantanés, votes, aperçu Markdown, sans application JavaScript à part'],
        ['name' => 'PostgreSQL', 'role' => 'Base de données et recherche plein texte en français'],
        ['name' => 'Tailwind CSS 4', 'role' => 'Styles, thème clair et sombre'],
        ['name' => 'Pest', 'role' => 'Tests automatisés'],
    ],

    'packages' => [
        // Composer (dépendances directes, puis bibliothèques clés)
        ['package' => 'laravel/framework', 'type' => 'composer', 'name' => 'Laravel', 'usage' => 'Framework'],
        ['package' => 'livewire/livewire', 'type' => 'composer', 'name' => 'Livewire (avec Alpine.js, MIT)', 'usage' => 'Interactivité'],
        ['package' => 'laravel/fortify', 'type' => 'composer', 'name' => 'Laravel Fortify', 'usage' => 'Authentification'],
        ['package' => 'laravel/tinker', 'type' => 'composer', 'name' => 'Laravel Tinker', 'usage' => 'Console de développement'],
        ['package' => 'league/commonmark', 'type' => 'composer', 'name' => 'league/commonmark', 'usage' => 'Rendu Markdown sécurisé'],
        ['package' => 'scrivo/highlight.php', 'type' => 'composer', 'name' => 'highlight.php', 'usage' => 'Coloration du code, côté serveur'],
        ['package' => 'intervention/image', 'type' => 'composer', 'name' => 'Intervention Image', 'usage' => 'Avatars'],
        ['package' => 'nette/schema', 'type' => 'composer', 'name' => 'nette/schema', 'usage' => 'Dépendance indirecte de Laravel, utilisée sous BSD-3-Clause (triple licence au choix)'],
        ['package' => 'nette/utils', 'type' => 'composer', 'name' => 'nette/utils', 'usage' => 'Dépendance indirecte de Laravel, utilisée sous BSD-3-Clause (triple licence au choix)'],
        ['package' => 'pestphp/pest', 'type' => 'composer', 'name' => 'Pest', 'usage' => 'Tests (développement)'],
        ['package' => 'pestphp/pest-plugin-laravel', 'type' => 'composer', 'name' => 'Pest, extension Laravel', 'usage' => 'Tests (développement)'],
        ['package' => 'phpunit/phpunit', 'type' => 'composer', 'name' => 'PHPUnit', 'usage' => 'Tests (développement)'],
        ['package' => 'mockery/mockery', 'type' => 'composer', 'name' => 'Mockery', 'usage' => 'Tests (développement)'],
        ['package' => 'fakerphp/faker', 'type' => 'composer', 'name' => 'Faker', 'usage' => 'Données de test (développement)'],
        ['package' => 'laravel/pint', 'type' => 'composer', 'name' => 'Laravel Pint', 'usage' => 'Style du code (développement)'],
        ['package' => 'laravel/pail', 'type' => 'composer', 'name' => 'Laravel Pail', 'usage' => 'Lecture des journaux (développement)'],
        ['package' => 'nunomaduro/collision', 'type' => 'composer', 'name' => 'Collision', 'usage' => 'Affichage des erreurs (développement)'],
        // npm (outils de construction, non distribués)
        ['package' => 'tailwindcss', 'type' => 'npm', 'name' => 'Tailwind CSS', 'usage' => 'Styles'],
        ['package' => '@tailwindcss/vite', 'type' => 'npm', 'name' => 'Tailwind CSS pour Vite', 'usage' => 'Construction des styles'],
        ['package' => 'vite', 'type' => 'npm', 'name' => 'Vite', 'usage' => 'Construction des fichiers CSS et JS'],
        ['package' => 'laravel-vite-plugin', 'type' => 'npm', 'name' => 'Laravel Vite Plugin', 'usage' => 'Construction des fichiers CSS et JS'],
        ['package' => 'concurrently', 'type' => 'npm', 'name' => 'concurrently', 'usage' => 'Lancement des outils de développement'],
    ],

    'assets' => [
        ['name' => 'Newsreader', 'version' => 'Fontsource 5.3', 'license' => 'SIL OFL 1.1', 'usage' => 'Titres et logo'],
        ['name' => 'Schibsted Grotesk', 'version' => 'Fontsource 5.3', 'license' => 'SIL OFL 1.1', 'usage' => 'Texte et interface'],
        ['name' => 'JetBrains Mono', 'version' => 'Fontsource 5.3', 'license' => 'SIL OFL 1.1', 'usage' => 'Blocs de code'],
        ['name' => 'Lucide', 'version' => 'icônes recopiées en SVG', 'license' => 'ISC', 'usage' => 'Icônes'],
        ['name' => 'PostgreSQL', 'version' => '16', 'license' => 'PostgreSQL License', 'usage' => 'Base de données (service, non distribué)'],
    ],

    'ai' => [
        ['tool' => 'Claude Code (Anthropic)', 'parts' => 'Conception et documentation, écriture du code et des tests, revue de code. Chaque membre de l\'équipe relit le code et sait l\'expliquer.'],
        ['tool' => 'Générateur d\'images IA', 'parts' => 'Image du haut de l\'accueil (deux devs devant un ordinateur), validée par l\'équipe.'],
    ],
];
