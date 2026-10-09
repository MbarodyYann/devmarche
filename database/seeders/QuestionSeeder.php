<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Services\AnswerService;
use App\Services\VoteService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
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
                $askedAt = Carbon::now()->subDays($data['days'])->setTime(8 + $data['days'] % 12, 20);
                $question = $this->answers->ask($user($data['author']), $data['title'], $data['body'], $data['tags']);
                $question->forceFill(['created_at' => $askedAt, 'updated_at' => $askedAt])->save();

                foreach ($data['answers'] as $i => $answerData) {
                    $answer = $this->answers->answer($user($answerData['author']), $question, $answerData['body']);
                    $answeredAt = $askedAt->copy()->addHours(2 + $i * 7);
                    $answer->forceFill(['created_at' => $answeredAt, 'updated_at' => $answeredAt])->save();

                    if ($answerData['accepted'] ?? false) {
                        $this->answers->toggleAccepted($question, $answer);
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
        $count = min($count, $voters->count());

        for ($i = 0; $i < $count; $i++) {
            $this->votes->vote($voters[($offset + $i) % $voters->count()], $votable, 1);
        }

        return $count;
    }
}
