<?php

namespace App\Policies;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    /** Tout membre connecté peut poser une question. */
    public function create(User $user): bool
    {
        return true;
    }

    /** Tout membre connecté peut répondre. */
    public function answer(User $user, Question $question): bool
    {
        return true;
    }

    /** Seul l'auteur de la question accepte (ou retire) une réponse, qui doit appartenir à sa question. */
    public function accept(User $user, Question $question, Answer $answer): bool
    {
        return $question->user_id === $user->id && $answer->question_id === $question->id;
    }
}
