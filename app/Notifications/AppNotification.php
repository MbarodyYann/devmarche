<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Notification dans l'application (table notifications) : un texte court, un lien et une icône.
 * Le texte est figé à l'envoi : il reste juste même si le titre de la mission change ensuite.
 */
abstract class AppNotification extends Notification
{
    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{text: string, url: string, icon: string}
     */
    abstract public function toArray(object $notifiable): array;
}
