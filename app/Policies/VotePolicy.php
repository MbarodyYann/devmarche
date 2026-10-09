<?php

namespace App\Policies;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Règles de vote (02 et 04) : pas pour son propre contenu ; le vote négatif demande 50 cauris
 * (les Q&R doivent rester bienveillantes envers les débutants).
 */
class VotePolicy
{
    public const DOWNVOTE_MIN_REPUTATION = 50;

    public function vote(User $user, Question|Answer $votable, int $value): Response
    {
        if ($votable->user_id === $user->id) {
            return Response::deny('Vous ne pouvez pas voter pour votre propre contenu.');
        }

        if ($value < 0 && $user->reputation < self::DOWNVOTE_MIN_REPUTATION) {
            return Response::deny('Le vote négatif est ouvert à partir de '.self::DOWNVOTE_MIN_REPUTATION.' cauris, pour garder les échanges bienveillants.');
        }

        return Response::allow();
    }
}
