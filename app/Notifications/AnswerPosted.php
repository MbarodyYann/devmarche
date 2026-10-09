<?php

namespace App\Notifications;

use App\Models\Answer;

/** À l'auteur de la question : une nouvelle réponse. */
class AnswerPosted extends AppNotification
{
    public function __construct(private readonly Answer $answer) {}

    public function toArray(object $notifiable): array
    {
        return [
            'text' => $this->answer->user->name.' a répondu à votre question « '.$this->answer->question->title.' ».',
            'url' => route('questions.show', $this->answer->question_id).'#reponse-'.$this->answer->id,
            'icon' => 'message',
        ];
    }
}
