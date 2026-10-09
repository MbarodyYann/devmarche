<?php

namespace App\Notifications;

use App\Models\Mission;
use App\Services\ReputationService;
use App\Support\Money;

/** Au dev : le client a confirmé la fin de la mission, cauris crédités. */
class MissionCompleted extends AppNotification
{
    public function __construct(
        private readonly Mission $mission,
        private readonly ?int $releasedAmount = null,
    ) {}

    public function toArray(object $notifiable): array
    {
        return [
            'text' => 'Mission « '.$this->mission->title.' » terminée : +'.ReputationService::MISSION_COMPLETED.' cauris'
                .($this->releasedAmount ? ', et '.Money::fcfa($this->releasedAmount).' FCFA libérés (paiement simulé).' : '.'),
            'url' => route('missions.show', $this->mission),
            'icon' => 'check',
        ];
    }
}
