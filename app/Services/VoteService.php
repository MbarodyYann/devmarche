<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;

/**
 * Votes +1 / −1 sur les questions et les réponses.
 *
 * - Revoter dans le même sens annule le vote ; voter dans l'autre sens l'inverse.
 * - Le score dénormalisé (score) suit chaque changement.
 * - Les votes sur une RÉPONSE rapportent ou retirent des cauris à son auteur
 *   (+10 voté, −2 voté négativement) ; une annulation ou une inversion crée l'événement inverse.
 *   Les votes sur une question ne touchent que son score.
 * Les autorisations (pas son propre contenu, vote négatif à 50 cauris) sont vérifiées par VotePolicy.
 */
class VoteService
{
    public const ANSWER_UPVOTED = 10;

    public const ANSWER_DOWNVOTED = -2;

    public function __construct(private readonly ReputationService $reputation) {}

    /**
     * @return int La valeur du vote de l'utilisateur après l'action : 1, -1 ou 0 (aucun vote).
     */
    public function vote(User $user, Question|Answer $votable, int $value): int
    {
        $value = $value > 0 ? 1 : -1;

        return DB::transaction(function () use ($user, $votable, $value) {
            $votable->newQuery()->whereKey($votable->getKey())->lockForUpdate()->first();

            /** @var Vote|null $existing */
            $existing = $votable->votes()->where('user_id', $user->id)->first();
            $previous = $existing?->value ?? 0;
            $next = $previous === $value ? 0 : $value; // même sens : annulation

            if ($next === 0) {
                $existing->delete();
            } elseif ($existing) {
                $existing->update(['value' => $next]);
            } else {
                $vote = $votable->votes()->make(['value' => $next]);
                $vote->user()->associate($user);
                $vote->save();
            }

            $votable->newQuery()->whereKey($votable->getKey())->increment('score', $next - $previous);
            $votable->score += $next - $previous;

            if ($votable instanceof Answer) {
                $this->adjustReputation($votable, $previous, $next);
            }

            return $next;
        });
    }

    /**
     * Annule l'effet de l'ancien vote, puis applique le nouveau (chaque étape laisse une trace).
     */
    private function adjustReputation(Answer $answer, int $previous, int $next): void
    {
        $author = $answer->user()->firstOrFail();

        if ($previous === 1) {
            $this->reputation->award($author, -self::ANSWER_UPVOTED, 'answer_upvote_cancelled', $answer);
        } elseif ($previous === -1) {
            $this->reputation->award($author, -self::ANSWER_DOWNVOTED, 'answer_downvote_cancelled', $answer);
        }

        if ($next === 1) {
            $this->reputation->award($author, self::ANSWER_UPVOTED, 'answer_upvoted', $answer);
        } elseif ($next === -1) {
            $this->reputation->award($author, self::ANSWER_DOWNVOTED, 'answer_downvoted', $answer);
        }
    }
}
