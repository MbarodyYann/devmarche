<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Dernière activité (« Actif aujourd'hui », « Actif il y a 3 j ») : déduite de l'activité réelle
 * de chaque membre dans le jeu de démo (inscription, publications, candidatures, réponses, votes),
 * donc toujours cohérente avec le reste des données.
 */
class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement(<<<'SQL'
            update users set last_seen_at = least(now() - interval '5 minutes', greatest(
                users.created_at + interval '40 minutes',
                (select max(created_at) from missions where client_id = users.id),
                (select max(created_at) from applications where user_id = users.id),
                (select max(created_at) from questions where user_id = users.id),
                (select max(created_at) from answers where user_id = users.id),
                (select max(created_at) from votes where user_id = users.id)
            ))
            SQL);
    }
}
