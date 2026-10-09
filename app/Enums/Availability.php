<?php

namespace App\Enums;

enum Availability: string
{
    case Available = 'available';
    case Busy = 'busy';
    case Employed = 'employed';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::Busy => 'Occupé',
            self::Employed => 'En poste',
        };
    }

    /**
     * Variante de pastille de statut (voir le composant x-status-pill).
     */
    public function tone(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Busy => 'primary',
            self::Employed => 'muted',
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
