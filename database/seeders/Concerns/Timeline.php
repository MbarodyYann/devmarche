<?php

namespace Database\Seeders\Concerns;

use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Chronologie cohérente du jeu de démonstration.
 *
 * Tout se passe sur les 30 derniers jours (config devmarche.demo_window_days), et jamais avant le
 * lancement (29 sept. 2026) : la démo réinitialisée chaque nuit reste « fraîche » le jour de la
 * notation (la mission la plus récente a moins de 24 h). Chaque action est exécutée
 * « à son heure » grâce à Carbon::setTestNow(), pour que les horodatages posés par Eloquent et
 * par les services (attribution, fin de mission, votes, événements de réputation) soient cohérents :
 * un contenu est toujours postérieur à l'inscription de son auteur, une réponse à sa question,
 * un vote à ce qui est voté.
 */
trait Timeline
{
    /** « Maintenant » réel, mémorisé avant tout voyage dans le temps. */
    private ?Carbon $timelineEnd = null;

    /** Début de la période de démo : max(lancement, maintenant − 30 jours). */
    protected function launch(): Carbon
    {
        return Carbon::parse(config('devmarche.launched_at'))
            ->max($this->latest()->subDays((int) config('devmarche.demo_window_days')));
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

    /** Publication d'une question « vieille de $days jours » (sur 95), jamais avant l'inscription de son auteur. */
    protected function questionAt(int $days, Carbon $authorJoined): Carbon
    {
        return $this->at(1 - $days / 95, $authorJoined->copy()->addHours(2));
    }

    /** Publication d'une mission « vieille de $days jours » (sur 95), jamais avant l'inscription du client. */
    protected function missionAt(int $days, Carbon $clientJoined): Carbon
    {
        return $this->at(1 - $days / 95, $clientJoined->copy()->addHours(3));
    }

    /** $i-ième réponse : quelques heures après la question, et après l'inscription de son auteur. */
    protected function answerAt(Carbon $askedAt, int $i, string $title, Carbon $answererJoined): Carbon
    {
        return $this->clamp($askedAt->copy()->addMinutes(50 + 170 * $i + crc32($title) % 60)->max($answererJoined->copy()->addHour()));
    }

    /** $i-ième candidature : quelques heures après la publication, et après l'inscription du candidat. */
    protected function applicationAt(Carbon $publishedAt, int $i, Carbon $applicantJoined): Carbon
    {
        return $this->clamp($publishedAt->copy()->addMinutes(40 + 150 * $i)->max($applicantJoined->copy()->addHour()));
    }

    /**
     * Ramène l'action d'un membre avant sa dernière activité prévue (ActivitySeeder::plan),
     * sans jamais passer avant $after (la question à laquelle il répond, la mission à laquelle il postule).
     */
    protected function byActor(User $actor, Carbon $moment, Carbon $after): Carbon
    {
        if ($actor->last_seen_at === null) {
            return $moment;
        }

        return $moment->copy()->min($actor->last_seen_at->copy()->subMinutes(15))->max($after);
    }

    /** Le membre est-il déjà parti (dernière activité prévue) à cet instant ? */
    protected function goneBefore(User $actor, Carbon $moment): bool
    {
        return $actor->last_seen_at !== null && $actor->last_seen_at->copy()->subMinutes(15)->lt($moment);
    }

    /**
     * Réponse indispensable à la démo : la réponse acceptée, ou la première si aucune ne l'est.
     * Les autres sont facultatives : un membre parti avant la question ne répond pas.
     *
     * @param  array<string, mixed>  $question
     */
    protected function requiredAnswer(array $question, int $i): bool
    {
        $accepted = collect($question['answers'])->search(fn (array $a) => $a['accepted'] ?? false);

        return $i === ($accepted === false ? 0 : $accepted);
    }

    /**
     * Candidature indispensable : celle du dev retenu, la première de chaque mission ouverte,
     * et toutes celles de la mission de la démo (Aïssatou compare les candidats). Les autres sont facultatives.
     *
     * @param  array<string, mixed>  $mission
     */
    protected function requiredApplication(array $mission, string $name): bool
    {
        $rank = array_search($name, $mission['applicants'] ?? [], true);

        return ($mission['demo'] ?? false)
            || ($mission['status'] === 'open' && $rank === 0)
            || $name === ($mission['freelancer'] ?? null);
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
        // On restaure l'horloge d'avant (celle d'un test qui simule une date, par exemple).
        $previous = Carbon::getTestNow();
        Carbon::setTestNow($moment);

        try {
            return $action();
        } finally {
            Carbon::setTestNow($previous);
        }
    }
}
