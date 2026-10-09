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
     * Ton de la puce (voir le composant x-chip et 08-design.md).
     */
    public function tone(): string
    {
        return match ($this) {
            self::Available => 'secondary',
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
