<?php

namespace App\Enums;

/** États du séquestre simulé. */
enum PaymentStatus: string
{
    case Held = 'held';
    case Released = 'released';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Held => 'Fonds sécurisés',
            self::Released => 'Paiement libéré au dev',
            self::Refunded => 'Remboursé',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Held => 'info',
            self::Released => 'info',
            self::Refunded => 'muted',
        };
    }
}
