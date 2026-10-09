<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Skill;
use App\Models\User;
use App\Notifications\AnswerAccepted;
use App\Notifications\AnswerPosted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Questions et réponses : publication et réponse acceptée.
 */
class AnswerService
{
    public const ANSWER_ACCEPTED = 15;

    public function __construct(private readonly ReputationService $reputation) {}

    /**
     * @param  list<string>  $tags
     */
    public function ask(User $user, string $title, string $body, array $tags): Question
    {
        return DB::transaction(function () use ($user, $title, $body, $tags) {
            $question = $user->questions()->create(['title' => $title, 'body' => $body]);
            $question->skills()->sync(
                collect($tags)->map(fn (string $name) => Skill::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id)
            );

            return $question;
        });
    }

    public function answer(User $user, Question $question, string $body): Answer
    {
        return DB::transaction(function () use ($user, $question, $body) {
            $answer = $question->answers()->make(['body' => $body]);
            $answer->user()->associate($user);
            $answer->save();

            $question->newQuery()->whereKey($question->getKey())->increment('answers_count');

            if ($question->user_id !== $user->id) {
                $answer->setRelation('user', $user)->setRelation('question', $question);
                $question->user()->firstOrFail()->notify(new AnswerPosted($answer));
            }

            return $answer;
        });
    }

    /**
     * Accepte une réponse (+15 cauris à son auteur). Si une autre réponse était acceptée, elle perd ses 15 cauris.
     * Accepter à nouveau la réponse déjà acceptée la retire (l'auteur de la question change d'avis).
     * L'auteur de la question ne gagne rien en acceptant sa propre réponse.
     */
    public function toggleAccepted(Question $question, Answer $answer): bool
    {
        return DB::transaction(function () use ($question, $answer) {
            $question = Question::whereKey($question->getKey())->lockForUpdate()->firstOrFail();
            $previous = $question->accepted_answer_id ? Answer::find($question->accepted_answer_id) : null;
            $accepting = $previous?->isNot($answer) ?? true;

            if ($previous) {
                $this->reward($question, $previous, -self::ANSWER_ACCEPTED, 'answer_unaccepted');
            }

            $question->forceFill(['accepted_answer_id' => $accepting ? $answer->id : null])->save();

            if ($accepting) {
                $this->reward($question, $answer, self::ANSWER_ACCEPTED, 'answer_accepted');

                if ($answer->user_id !== $question->user_id) {
                    $answer->setRelation('question', $question);
                    $answer->user()->firstOrFail()->notify(new AnswerAccepted($answer));
                }
            }

            return $accepting;
        });
    }

    private function reward(Question $question, Answer $answer, int $points, string $reason): void
    {
        if ($answer->user_id !== $question->user_id) {
            $this->reputation->award($answer->user()->firstOrFail(), $points, $reason, $answer);
        }
    }
}
