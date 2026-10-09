<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Services\AnswerService;
use App\Services\VoteService;
use Database\Seeders\Concerns\Timeline;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Environ 40 questions locales et crédibles (data/questions.php), avec leurs réponses, leurs réponses
 * acceptées et des votes. Tout passe par les vrais services (AnswerService, VoteService) : chaque cauri
 * correspond à un événement de réputation réel (vote reçu, réponse acceptée).
 *
 * Les votes sur les réponses sont répartis pour donner à chacun une réputation variée et crédible :
 * de 0 pour les nouveaux membres à environ 1 500 pour les plus actifs.
 */
class QuestionSeeder extends Seeder
{
    use Timeline;

    /** Réputation visée par membre (missions terminées comprises). Les autres restent à leur niveau. */
    private const TARGETS = [
        'Jean-Marc N\'Guessan' => 1480, 'Kouadio Yao' => 1210, 'Cheikh Fall' => 960, 'Fatoumata Camara' => 890,
        'Kossi Mensah' => 780, 'Paul Fotso' => 690, 'Arnaud Mbarga' => 640, 'Rodrigue Hounsou' => 540,
        'Christelle Ngo Biyong' => 500, 'Samuel Nkwenti' => 430, 'Fiacre Agossou' => 410, 'Mathieu Ahouansou' => 380,
        'Ibrahima Diop' => 350, 'Akossiwa Agbeko' => 320, 'Abdoul Kaboré' => 310, 'Bakary Coulibaly' => 260,
        'Kadiatou Keïta' => 240, 'Boukary Ouédraogo' => 220, 'Yawo Amegbor' => 200, 'Fatou Sow' => 190,
        'Awa Seck' => 170, 'Mamadou Bah' => 150, 'Moussa Ndiaye' => 180, 'Edem Afenyo' => 110,
        'Serge Kouassi' => 80, 'Grâce Adjovi' => 70, 'Mariam Traoré' => 40, 'Hawa Soumah' => 20,
    ];

    public function __construct(
        private readonly AnswerService $answers,
        private readonly VoteService $votes,
    ) {}

    public function run(): void
    {
        if (Question::query()->exists()) {
            return; // déjà semé : on ne duplique pas
        }

        $users = User::query()->orderBy('id')->get();
        $byName = $users->keyBy('name');
        $user = fn (string $name): User => $byName[$name] ?? throw new \RuntimeException("Dev inconnu dans les seeders : $name");

        DB::transaction(function () use ($users, $user) {
            /** @var Collection<int, Answer> $allAnswers */
            $allAnswers = collect();

            foreach (require __DIR__.'/data/questions.php' as $data) {
                $author = $user($data['author']);
                // Question : jamais avant l'inscription de son auteur.
                $askedAt = $this->at(1 - $data['days'] / 95, $author->created_at->copy()->addHours(2));
                $question = $this->travelTo($askedAt, fn () => $this->answers->ask($author, $data['title'], $data['body'], $data['tags']));

                foreach ($data['answers'] as $i => $answerData) {
                    $answerer = $user($answerData['author']);
                    // Réponse : après la question et après l'inscription de son auteur.
                    $answeredAt = $this->clamp($this->between($askedAt, 0.05 + 0.08 * $i)->max($answerer->created_at->copy()->addHour()));
                    $answer = $this->travelTo($answeredAt, fn () => $this->answers->answer($answerer, $question, $answerData['body']));

                    if ($answerData['accepted'] ?? false) {
                        $this->travelTo($this->between($answeredAt, 0.1), fn () => $this->answers->toggleAccepted($question, $answer));
                    }

                    $allAnswers->push($answer);
                }

                // Votes sur la question (n'affectent que son score).
                $this->voteMany($users, $question, $data['votes'], offset: $question->id * 3);
            }

            // Votes sur les réponses : on complète la réputation de chaque auteur jusqu'à sa cible.
            foreach ($allAnswers->groupBy('user_id') as $answers) {
                $author = $answers->first()->user()->first();
                $target = self::TARGETS[$author->name] ?? null;
                if ($target === null) {
                    continue;
                }

                $needed = max(0, intdiv($target - $author->fresh()->reputation, VoteService::ANSWER_UPVOTED));
                foreach ($answers->values() as $i => $answer) {
                    $share = (int) ceil($needed / ($answers->count() - $i));
                    $cast = $this->voteMany($users, $answer, $share, offset: $answer->id * 7);
                    $needed -= $cast;
                }
            }
        });
    }

    /**
     * Fait voter $count membres (jamais l'auteur) pour un contenu, en faisant tourner les votants.
     *
     * @param  Collection<int, User>  $users
     */
    private function voteMany(Collection $users, Question|Answer $votable, int $count, int $offset): int
    {
        $voters = $users->reject(fn (User $u) => $u->id === $votable->user_id)->values();

        $cast = 0;
        for ($i = 0; $i < $voters->count() && $cast < $count; $i++) {
            $voter = $voters[($offset + $i) % $voters->count()];
            // Un vote est postérieur au contenu ET à l'inscription du votant.
            $votedAt = $this->clamp($this->between($votable->created_at, ($cast + 1) / ($count + 2))->max($voter->created_at->copy()->addMinutes(30)));
            if ($votedAt->lt($votable->created_at)) {
                continue;
            }
            $this->travelTo($votedAt, fn () => $this->votes->vote($voter, $votable, 1));
            $cast++;
        }

        return $cast;
    }
}
