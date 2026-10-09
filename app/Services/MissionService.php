<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\MissionStatus;
use App\Models\Application;
use App\Models\Mission;
use App\Models\Skill;
use App\Models\User;
use App\Notifications\ApplicationDecided;
use App\Notifications\ApplicationReceived;
use App\Notifications\MissionCompleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Cycle de vie d'une mission : open → assigned → completed (ou cancelled).
 * Les autorisations sont vérifiées en amont par MissionPolicy ; ici, on garantit la cohérence.
 */
class MissionService
{
    public function __construct(private readonly ReputationService $reputation) {}

    /**
     * @param  array{title: string, description: string, budget_fcfa: int, country: ?string, deadline: ?string}  $data
     * @param  list<string>  $skillNames
     */
    public function publish(User $client, array $data, array $skillNames): Mission
    {
        return DB::transaction(function () use ($client, $data, $skillNames) {
            $mission = $client->missions()->create($data);
            $mission->skills()->sync(
                collect($skillNames)->map(fn (string $name) => Skill::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id)
            );

            return $mission;
        });
    }

    /**
     * Candidature d'un dev ; le client est prévenu.
     *
     * @param  array{message: string, proposed_fcfa: int, delivery_days?: int|null}  $data
     */
    public function apply(Mission $mission, User $dev, array $data): Application
    {
        $application = $mission->applications()->make($data);
        $application->user()->associate($dev);
        $application->save();

        $application->setRelation('mission', $mission);
        $mission->client()->firstOrFail()->notify(new ApplicationReceived($application));

        return $application;
    }

    /**
     * Retient un candidat : la mission passe en cours, les autres candidatures sont refusées,
     * et tous les candidats sont prévenus.
     */
    public function accept(Mission $mission, Application $application): void
    {
        DB::transaction(function () use ($mission, $application) {
            // Verrouille la ligne : deux clics simultanés sur « Retenir » ne peuvent pas attribuer deux fois.
            Mission::whereKey($mission->getKey())->lockForUpdate()->firstOrFail();

            $mission->forceFill([
                'status' => MissionStatus::Assigned,
                'freelancer_id' => $application->user_id,
                'assigned_at' => now(),
            ])->save();

            $application->forceFill(['status' => ApplicationStatus::Accepted])->save();

            $others = $mission->applications()
                ->whereKeyNot($application->getKey())
                ->where('status', ApplicationStatus::Pending);
            $rejected = User::whereIn('id', (clone $others)->select('user_id'))->get();
            $others->update(['status' => ApplicationStatus::Rejected, 'updated_at' => now()]);

            User::findOrFail($application->user_id)->notify(new ApplicationDecided($mission, accepted: true));
            Notification::send($rejected, new ApplicationDecided($mission, accepted: false));
        });
    }

    /**
     * Termine la mission et crédite le freelance de ses cauris.
     */
    public function complete(Mission $mission): void
    {
        DB::transaction(function () use ($mission) {
            $mission->forceFill([
                'status' => MissionStatus::Completed,
                'completed_at' => now(),
            ])->save();

            $freelancer = $mission->freelancer()->firstOrFail();
            $this->reputation->award($freelancer, ReputationService::MISSION_COMPLETED, 'mission_completed', $mission);
            $freelancer->notify(new MissionCompleted($mission));
        });
    }

    public function cancel(Mission $mission): void
    {
        DB::transaction(function () use ($mission) {
            $mission->forceFill(['status' => MissionStatus::Cancelled])->save();

            $pending = $mission->applications()->where('status', ApplicationStatus::Pending);
            $applicants = User::whereIn('id', (clone $pending)->select('user_id'))->get();
            $pending->update(['status' => ApplicationStatus::Rejected, 'updated_at' => now()]);

            Notification::send($applicants, new ApplicationDecided($mission, accepted: false, cancelled: true));
        });
    }
}
