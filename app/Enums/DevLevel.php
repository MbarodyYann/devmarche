<?php

namespace App\Enums;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Niveau d'un dev, calculé automatiquement à partir de son activité (idée reprise de Fiverr, 02).
 *
 * - Référence : au moins 1 000 cauris et 5 missions terminées ;
 * - Expert : au moins 500 cauris et 3 missions terminées ;
 * - Confirmé : au moins 100 cauris ou 1 mission terminée ;
 * - Nouveau : inscrit depuis moins de 14 jours, sans encore être Confirmé.
 * Au-delà de 14 jours sans atteindre « Confirmé », aucun niveau n'est affiché.
 */
enum DevLevel: string
{
    case Newcomer = 'nouveau';
    case Confirmed = 'confirme';
    case Expert = 'expert';
    case Reference = 'reference';

    public function label(): string
    {
        return match ($this) {
            self::Newcomer => 'Nouveau',
            self::Confirmed => 'Confirmé',
            self::Expert => 'Expert',
            self::Reference => 'Référence',
        };
    }

    /**
     * Ton de l'étiquette (texte seul, sans pictogramme).
     */
    public function tone(): string
    {
        return match ($this) {
            self::Newcomer => 'warm',
            self::Confirmed => 'neutral',
            self::Expert, self::Reference => 'info',
        };
    }

    public static function for(User $user): ?self
    {
        $completed = $user->completedMissionsCount();

        return match (true) {
            $user->reputation >= 1000 && $completed >= 5 => self::Reference,
            $user->reputation >= 500 && $completed >= 3 => self::Expert,
            $user->reputation >= 100 || $completed >= 1 => self::Confirmed,
            $user->created_at !== null && $user->created_at->gt(now()->subDays((int) config('devmarche.new_member_days'))) => self::Newcomer,
            default => null,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect([self::Reference, self::Expert, self::Confirmed, self::Newcomer])
            ->mapWithKeys(fn (self $level) => [$level->value => $level->label()])
            ->all();
    }

    /**
     * Restreint une requête de membres à ce niveau exact (filtre de l'annuaire).
     *
     * @param  Builder<User>  $query
     */
    public function scope(Builder $query): void
    {
        $completed = '(select count(*) from missions where missions.freelancer_id = users.id and missions.status = \'completed\')';
        $newSince = now()->subDays((int) config('devmarche.new_member_days'));

        // Le niveau ne concerne que les membres qui ont une activité de dev (une réponse ou une candidature).
        $query->where(fn (Builder $q) => $q->whereHas('answers')->orWhereHas('applications'));

        match ($this) {
            self::Reference => $query->where('reputation', '>=', 1000)->whereRaw("$completed >= 5"),
            self::Expert => $query->where('reputation', '>=', 500)->whereRaw("$completed >= 3")
                ->whereNot(fn (Builder $q) => $q->where('reputation', '>=', 1000)->whereRaw("$completed >= 5")),
            self::Confirmed => $query->where(fn (Builder $q) => $q->where('reputation', '>=', 100)->orWhereRaw("$completed >= 1"))
                ->whereNot(fn (Builder $q) => $q->where('reputation', '>=', 500)->whereRaw("$completed >= 3")),
            self::Newcomer => $query->where('reputation', '<', 100)->whereRaw("$completed = 0")->where('created_at', '>', $newSince),
        };
    }
}
