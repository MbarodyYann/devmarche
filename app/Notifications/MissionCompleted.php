<?php

namespace App\Notifications;

use App\Models\Mission;
use App\Services\ReputationService;

/** Au dev : le client a confirmé la fin de la mission, cauris crédités. */
class MissionCompleted extends AppNotification
{
    public function __construct(private readonly Mission $mission) {}

    public function toArray(object $notifiable): array
    {
        return [
            'text' => 'Mission « '.$this->mission->title.' » terminée : +'.ReputationService::MISSION_COMPLETED.' cauris.',
            'url' => route('missions.show', $this->mission),
            'icon' => 'check',
        ];
    }
}
