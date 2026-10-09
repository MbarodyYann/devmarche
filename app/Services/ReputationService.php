<?php

namespace App\Services;

use App\Models\ReputationEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Seul point d'entrée des variations de réputation : chaque variation laisse une trace
 * dans reputation_events et met à jour users.reputation, dans la même transaction.
 */
class ReputationService
{
    /** Points gagnés par le freelance quand sa mission est terminée. */
    public const MISSION_COMPLETED = 20;

    public function award(User $user, int $points, string $reason, ?Model $source = null): ReputationEvent
    {
        return DB::transaction(function () use ($user, $points, $reason, $source) {
            $event = new ReputationEvent(['points' => $points, 'reason' => $reason]);
            $event->user()->associate($user);

            if ($source) {
                $event->source()->associate($source);
            }

            $event->save();

            User::whereKey($user->getKey())->increment('reputation', $points);
            $user->reputation += $points;

            return $event;
        });
    }

    /**
     * Recalcule users.reputation à partir des événements. Renvoie le nombre de comptes corrigés.
     */
    public function recalculate(): int
    {
        return DB::transaction(fn () => DB::update(<<<'SQL'
            update users
            set reputation = coalesce(totals.points, 0)
            from (
                select u.id, sum(e.points) as points
                from users u
                left join reputation_events e on e.user_id = u.id
                group by u.id
            ) as totals
            where totals.id = users.id
              and users.reputation <> coalesce(totals.points, 0)
            SQL));
    }
}
