<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Carbon;

/**
 * Chronologie cohérente du jeu de démonstration.
 *
 * Tout se passe entre le lancement (29 sept. 2026) et maintenant. Chaque action est exécutée
 * « à son heure » grâce à Carbon::setTestNow(), pour que les horodatages posés par Eloquent et
 * par les services (attribution, fin de mission, votes, événements de réputation) soient cohérents :
 * un contenu est toujours postérieur à l'inscription de son auteur, une réponse à sa question,
 * un vote à ce qui est voté.
 */
trait Timeline
{
    /** « Maintenant » réel, mémorisé avant tout voyage dans le temps. */
    private ?Carbon $timelineEnd = null;

    protected function launch(): Carbon
    {
        return Carbon::parse(config('devmarche.launched_at'));
    }

    /** Dernier instant utilisable (un peu avant maintenant, pour que tout soit « passé »). */
    protected function latest(): Carbon
    {
        $this->timelineEnd ??= Carbon::now()->subMinutes(20);

        return $this->timelineEnd->copy();
    }

    /**
     * Instant situé à $fraction (0 = lancement, 1 = maintenant) de la période de démonstration,
     * jamais avant $notBefore.
     */
    protected function at(float $fraction, ?Carbon $notBefore = null): Carbon
    {
        $start = $this->launch();
        $span = $start->diffInSeconds($this->latest());
        $moment = $start->copy()->addSeconds((int) round($span * max(0.0, min(1.0, $fraction))));

        return $this->clamp($notBefore ? $moment->max($notBefore) : $moment);
    }

    /** Ramène un instant dans la période [lancement, maintenant]. */
    protected function clamp(Carbon $moment): Carbon
    {
        return $moment->copy()->max($this->launch())->min($this->latest());
    }

    /** Point situé à $share du chemin entre $from et maintenant. */
    protected function between(Carbon $from, float $share): Carbon
    {
        $span = max(0, $from->diffInSeconds($this->latest(), false));

        return $this->clamp($from->copy()->addSeconds((int) round($span * $share)));
    }

    /**
     * Exécute $action comme si l'horloge marquait $moment.
     *
     * @template T
     *
     * @param  callable(): T  $action
     * @return T
     */
    protected function travelTo(Carbon $moment, callable $action): mixed
    {
        Carbon::setTestNow($moment);

        try {
            return $action();
        } finally {
            Carbon::setTestNow();
        }
    }
}
