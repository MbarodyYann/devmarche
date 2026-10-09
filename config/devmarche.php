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

    /** Un membre est « Nouveau » pendant ce nombre de jours, tant qu'il n'est pas « Confirmé ». */
    'new_member_days' => 14,

];
