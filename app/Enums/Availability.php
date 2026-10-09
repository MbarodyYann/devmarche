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
     * Libellé court du tampon (voir le composant x-stamp).
     */
    public function stamp(): string
    {
        return match ($this) {
            self::Available => 'Dispo',
            self::Busy => 'Occupé',
            self::Employed => 'En poste',
        };
    }

    /**
     * Couleur d'encre du tampon (voir 08-design.md).
     */
    public function tone(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Busy => 'muted',
            self::Employed => 'secondary',
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
