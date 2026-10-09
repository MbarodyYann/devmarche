<?php

namespace App\Notifications;

use App\Models\Application;
use App\Support\Money;

/** Au client : un dev a postulé à sa mission. */
class ApplicationReceived extends AppNotification
{
    public function __construct(private readonly Application $application) {}

    public function toArray(object $notifiable): array
    {
        $mission = $this->application->mission;

        return [
            'text' => $this->application->user->name.' a postulé à « '.$mission->title.' » pour '.Money::fcfa($this->application->proposed_fcfa).' FCFA.',
            'url' => route('missions.show', $mission).'#candidatures',
            'icon' => 'briefcase',
        ];
    }
}
