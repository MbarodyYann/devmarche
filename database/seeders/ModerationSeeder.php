<?php

namespace Database\Seeders;

use App\Enums\ReportReason;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Report;
use App\Models\User;
use App\Support\Demo;
use Database\Seeders\Concerns\Timeline;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Modération de démonstration : le compte admin (sans profil public) et quelques signalements
 * à traiter dans /moderation.
 */
class ModerationSeeder extends Seeder
{
    use Timeline;

    public function run(): void
    {
        $this->travelTo($this->launch(), fn () => User::updateOrCreate(
            ['email' => Demo::ADMIN_EMAIL],
            ['name' => 'Modération DevMarché', 'password' => Hash::make('password'), 'email_verified_at' => now(), 'is_admin' => true],
        ));

        if (Report::query()->exists()) {
            return; // déjà semé : on ne duplique pas
        }

        $reporters = User::query()->whereHas('profile')->orderBy('id')->get();
        $question = Question::query()->orderByDesc('created_at')->first();
        $answer = Answer::query()
            ->whereNotIn('id', Question::query()->whereNotNull('accepted_answer_id')->select('accepted_answer_id'))
            ->orderByDesc('created_at')
            ->first();

        // Une question signalée 3 fois, une réponse signalée 2 fois.
        foreach ([[$question, 'question', [ReportReason::OffTopic, ReportReason::Spam, ReportReason::OffTopic]], [$answer, 'answer', [ReportReason::Offensive, ReportReason::Other]]] as [$content, $type, $reasons]) {
            if (! $content) {
                continue;
            }

            $candidates = $reporters->reject(fn (User $u) => $u->id === $content->user_id)->values();
            foreach ($reasons as $i => $reason) {
                $reporter = $candidates[($content->id * 7 + $i * 5) % $candidates->count()];
                $at = $this->clamp($content->created_at->copy()->addHours(2 + $i * 3)->max($reporter->created_at->copy()->addHour()));

                $this->travelTo($at, function () use ($reporter, $type, $content, $reason) {
                    $report = new Report(['reason' => $reason]);
                    $report->reporter()->associate($reporter);
                    $report->reportable_type = $type;
                    $report->reportable_id = $content->id;
                    $report->save();
                });
            }
        }
    }
}
