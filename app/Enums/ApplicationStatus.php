<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Accepted => 'Retenue',
            self::Rejected => 'Non retenue',
        };
    }

    /**
     * Ton de la puce (x-chip) : info, warm, done ou muted.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Pending => 'warm',
            self::Accepted => 'done',
            self::Rejected => 'muted',
        };
    }
}
