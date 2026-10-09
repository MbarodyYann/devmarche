<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Concerns\Timeline;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Dernière activité (« Actif aujourd'hui », « Actif il y a 3 j »), fixée indépendamment des votes
 * pour que l'indicateur ait du sens : environ 40 % aujourd'hui, 20 % hier, 25 % entre 2 et 7 jours,
 * 15 % entre 8 et 20 jours. Les comptes de démo (Moussa, Aïssatou) sont actifs aujourd'hui.
 *
 * En deux temps :
 * 1. plan(), appelé par DevSeeder juste après les inscriptions, choisit la dernière activité de chacun.
 *    Les seeders de contenu s'y tiennent (Timeline::byActor) : un membre absent depuis 5 jours
 *    n'a rien publié depuis 5 jours. Les « absents » sont choisis parmi les membres dont toutes les
 *    réponses et candidatures peuvent tenir avant leur départ (questions et missions assez anciennes).
 * 2. run(), en fin de semis, garde-fou : la repousse si un contenu visible (mission, candidature,
 *    question, réponse) est malgré tout plus récent. Les votes sont anonymes et ne comptent pas.
 */
class ActivitySeeder extends Seeder
{
    use Timeline;

    /** Créneaux [part des membres, jours minimum, jours maximum], du plus ancien au plus récent. */
    public const BUCKETS = [
        [0.15, 8, 20],
        [0.25, 2, 7],
        [0.20, 1, 1],
        [0.40, 0, 0],
    ];

    /**
     * Dernière activité prévue de chaque membre, indexée par id.
     *
     * @param  Collection<int, User>  $users
     * @return array<int, Carbon>
     */
    public function plan(Collection $users): array
    {
        $now = Carbon::now();
        $ceiling = $now->copy()->subMinutes(5);
        $demo = [DevSeeder::DEMO_DEV_EMAIL, DevSeeder::DEMO_STARTUP_EMAIL];
        $joined = $users->mapWithKeys(fn (User $u) => [$u->name => $u->created_at]);

        // Instant le plus tôt où chacun peut partir : après son inscription, après chaque question dont il
        // donne la réponse indispensable et chaque mission qu'il décroche (à leurs heures « naturelles »).
        // Ses autres réponses et candidatures sont omises s'il est parti avant (Timeline::goneBefore).
        $earliest = $users->mapWithKeys(fn (User $u) => [$u->name => $u->created_at->copy()->addMinutes(40)])->all();
        $after = function (string $name, Carbon $moment) use (&$earliest) {
            $earliest[$name] = $earliest[$name]->max($moment->copy()->addMinutes(40));
        };

        foreach (require __DIR__.'/data/questions.php' as $question) {
            $askedAt = $this->questionAt($question['days'], $joined[$question['author']]);
            foreach ($question['answers'] as $i => $answer) {
                if (! $this->requiredAnswer($question, $i)) {
                    continue;
                }
                $after($answer['author'], $askedAt->max($joined[$answer['author']]->copy()->addHour()));
            }
        }

        foreach (MissionSeeder::missions() as $mission) {
            $publishedAt = $this->missionAt($mission['days'], $joined[$mission['client']]);
            foreach (array_filter($mission['applicants'] ?? [], fn (string $name) => $this->requiredApplication($mission, $name)) as $name) {
                $after($name, $publishedAt->max($joined[$name]->copy()->addHour()));
            }
        }

        // Ordre stable ; les comptes de démo restent « aujourd'hui ».
        $pool = $users->reject(fn (User $u) => in_array($u->email, $demo, true))
            ->sortBy(fn (User $u) => [$earliest[$u->name], crc32($u->email)])
            ->values();

        $planned = [];
        $carry = 0; // places d'un créneau ancien que personne ne pouvait prendre : reportées sur le suivant
        foreach (self::BUCKETS as [$share, $minDays, $maxDays]) {
            $quota = $minDays === 0 ? $pool->count() : (int) round($users->count() * $share) + $carry;
            $latestDay = $now->copy()->subDays($minDays)->setTime(20, 0)->min($ceiling);
            $chosen = $pool->filter(fn (User $u) => $earliest[$u->name]->lte($latestDay))
                ->take($quota)
                ->values();
            $carry = $quota - $chosen->count();

            foreach ($chosen as $position => $user) {
                $days = $minDays + ($position % ($maxDays - $minDays + 1));
                $target = $days === 0
                    // Aujourd'hui : de quelques minutes à quelques heures, sans déborder sur hier.
                    ? $now->copy()->subMinutes(10 + ($user->id * 37) % 300)->max($now->copy()->startOfDay())
                    // Sinon en journée, entre 8 h et 20 h.
                    : $now->copy()->subDays($days)->setTime(8 + $user->id % 13, ($user->id * 7) % 60);

                $planned[$user->id] = $target->max($earliest[$user->name])->min($ceiling);
            }

            $pool = $pool->reject(fn (User $u) => isset($planned[$u->id]))->values();
        }

        foreach ($users->filter(fn (User $u) => in_array($u->email, $demo, true)) as $user) {
            $planned[$user->id] = $now->copy()->subMinutes(10 + ($user->id * 37) % 60)->max($earliest[$user->name])->min($ceiling);
        }

        return $planned;
    }

    public function run(): void
    {
        // L'heure vient de Laravel (et non du now() de PostgreSQL), pour suivre une date simulée.
        DB::update(<<<'SQL'
            update users set last_seen_at = least(?::timestamp, greatest(
                coalesce(users.last_seen_at, users.created_at + interval '40 minutes'),
                (select max(created_at) from missions where client_id = users.id),
                (select max(created_at) from applications where user_id = users.id),
                (select max(created_at) from questions where user_id = users.id),
                (select max(created_at) from answers where user_id = users.id),
                (select max(created_at) from reviews where reviewer_id = users.id)
            ))
            SQL, [Carbon::now()->subMinutes(5)->toDateTimeString()]);
    }
}
