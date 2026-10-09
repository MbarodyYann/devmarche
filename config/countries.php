<?php

/*
|--------------------------------------------------------------------------
| Pays
|--------------------------------------------------------------------------
|
| Codes ISO 3166-1 alpha-2 => nom affiché. Les pays d'Afrique francophone
| sont listés en premier (ordre alphabétique), puis les autres pays où
| vivent souvent des devs de la diaspora. Utiliser App\Support\Countries
| plutôt que de lire ce fichier directement.
|
*/

return [

    'francophone' => [
        'DZ' => 'Algérie',
        'BJ' => 'Bénin',
        'BF' => 'Burkina Faso',
        'BI' => 'Burundi',
        'CM' => 'Cameroun',
        'CF' => 'Centrafrique',
        'KM' => 'Comores',
        'CG' => 'Congo',
        'CI' => 'Côte d\'Ivoire',
        'DJ' => 'Djibouti',
        'GA' => 'Gabon',
        'GN' => 'Guinée',
        'GQ' => 'Guinée équatoriale',
        'MG' => 'Madagascar',
        'ML' => 'Mali',
        'MA' => 'Maroc',
        'MU' => 'Maurice',
        'MR' => 'Mauritanie',
        'NE' => 'Niger',
        'CD' => 'RD Congo',
        'RW' => 'Rwanda',
        'SN' => 'Sénégal',
        'SC' => 'Seychelles',
        'TD' => 'Tchad',
        'TG' => 'Togo',
        'TN' => 'Tunisie',
    ],

    'others' => [
        'ZA' => 'Afrique du Sud',
        'BE' => 'Belgique',
        'CA' => 'Canada',
        'CV' => 'Cap-Vert',
        'EG' => 'Égypte',
        'US' => 'États-Unis',
        'ET' => 'Éthiopie',
        'FR' => 'France',
        'GM' => 'Gambie',
        'GH' => 'Ghana',
        'GW' => 'Guinée-Bissau',
        'KE' => 'Kenya',
        'LU' => 'Luxembourg',
        'NG' => 'Nigeria',
        'GB' => 'Royaume-Uni',
        'CH' => 'Suisse',
    ],

];
