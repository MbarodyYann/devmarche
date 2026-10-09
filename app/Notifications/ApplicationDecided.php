<?php

namespace App\Notifications;

use App\Models\Mission;

/** Au dev : sa candidature est retenue, ou non retenue (un autre dev a été choisi, ou la mission est annulée). */
class ApplicationDecided extends AppNotification
{
    public function __construct(
        private readonly Mission $mission,
        private readonly bool $accepted,
        private readonly bool $cancelled = false,
    ) {}

    public function toArray(object $notifiable): array
    {
        return match (true) {
            $this->accepted => [
                'text' => 'Bonne nouvelle : vous êtes retenu pour « '.$this->mission->title.' ».',
                'url' => route('missions.show', $this->mission),
                'icon' => 'check',
            ],
            $this->cancelled => [
                'text' => 'La mission « '.$this->mission->title.' » a été annulée par le client.',
                'url' => route('missions.index', ['pour_moi' => 1]),
                'icon' => 'x',
            ],
            default => [
                'text' => 'Votre candidature pour « '.$this->mission->title.' » n\'a pas été retenue. D\'autres missions vous attendent.',
                'url' => route('missions.index', ['pour_moi' => 1]),
                'icon' => 'briefcase',
            ],
        };
    }
}
