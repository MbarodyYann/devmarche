<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lancement de la plateforme
    |--------------------------------------------------------------------------
    |
    | Le concours CADEV a commencé le 29 septembre 2026 et DevMarché a été créé
    | pendant le concours : aucune donnée de démonstration n'est antérieure à
    | cette date (voir database/seeders et tests/Feature/DemoTimelineTest.php).
    |
    */

    'launched_at' => env('DEVMARCHE_LAUNCHED_AT', '2026-09-29 08:00:00'),

    /** Les données de démo s'étalent sur ce nombre de jours avant la réinitialisation (jamais avant le lancement). */
    'demo_window_days' => 30,

    /** Un membre est « Nouveau » pendant ce nombre de jours, tant qu'il n'est pas « Confirmé ». */
    'new_member_days' => 14,

    /*
    |--------------------------------------------------------------------------
    | Mode démo (jury)
    |--------------------------------------------------------------------------
    |
    | Connexion en un clic avec les comptes de démonstration, bandeau « mode démo »
    | et réinitialisation chaque nuit (php artisan demo:reset). À n'activer que
    | sur l'environnement présenté au jury.
    |
    */

    'demo' => (bool) env('APP_DEMO', false),

];
