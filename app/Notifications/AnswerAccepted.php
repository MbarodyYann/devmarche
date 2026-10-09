<?php

namespace App\Notifications;

use App\Models\Answer;
use App\Services\AnswerService;

/** À l'auteur de la réponse : elle est acceptée, cauris crédités. */
class AnswerAccepted extends AppNotification
{
    public function __construct(private readonly Answer $answer) {}

    public function toArray(object $notifiable): array
    {
        return [
            'text' => 'Votre réponse à « '.$this->answer->question->title.' » a été acceptée : +'.AnswerService::ANSWER_ACCEPTED.' cauris.',
            'url' => route('questions.show', $this->answer->question_id).'#reponse-'.$this->answer->id,
            'icon' => 'check',
        ];
    }
}
