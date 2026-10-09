<?php

namespace App\Enums;

enum MissionStatus: string
{
    case Open = 'open';
    case Assigned = 'assigned';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Ouverte',
            self::Assigned => 'En cours',
            self::Completed => 'Terminée',
            self::Cancelled => 'Annulée',
        };
    }

    /**
     * Ton de la puce (x-chip) : info, warm, done ou muted.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Open => 'info',
            self::Assigned => 'warm',
            self::Completed => 'done',
            self::Cancelled => 'muted',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
