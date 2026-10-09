<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Enums\MissionStatus;
use App\Models\Application;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Règles métier des missions (04-modele-donnees.md, « Règles métier importantes »).
 */
class MissionPolicy
{
    /** Tout membre connecté peut publier : pas de rôle figé. */
    public function create(User $user): bool
    {
        return true;
    }

    /** Postuler : mission ouverte, pas la sienne, une seule fois. */
    public function apply(User $user, Mission $mission): Response
    {
        if ($mission->client_id === $user->id) {
            return Response::deny('Vous ne pouvez pas postuler à votre propre mission.');
        }

        if ($mission->status !== MissionStatus::Open) {
            return Response::deny('Cette mission n\'accepte plus de candidatures.');
        }

        if ($mission->applications()->where('user_id', $user->id)->exists()) {
            return Response::deny('Vous avez déjà postulé à cette mission.');
        }

        return Response::allow();
    }

    /** Retenir un candidat : seul le client, sur une mission ouverte, une candidature en attente de cette mission. */
    public function accept(User $user, Mission $mission, Application $application): bool
    {
        return $mission->client_id === $user->id
            && $mission->status === MissionStatus::Open
            && $application->mission_id === $mission->id
            && $application->status === ApplicationStatus::Pending;
    }

    /** Terminer : seul le client, une mission attribuée. */
    public function complete(User $user, Mission $mission): bool
    {
        return $mission->client_id === $user->id
            && $mission->status === MissionStatus::Assigned;
    }

    /** Annuler : seul le client, tant que la mission n'est ni terminée ni déjà annulée. */
    public function cancel(User $user, Mission $mission): bool
    {
        return $mission->client_id === $user->id
            && in_array($mission->status, [MissionStatus::Open, MissionStatus::Assigned], true);
    }

    /** Voir la liste des candidatures : seul le client. */
    public function viewApplications(User $user, Mission $mission): bool
    {
        return $mission->client_id === $user->id;
    }

    /** Noter le freelance : seul le client, une mission terminée, une seule fois. */
    public function review(User $user, Mission $mission): bool
    {
        return $mission->client_id === $user->id
            && $mission->status === MissionStatus::Completed
            && $mission->freelancer_id !== null
            && ! $mission->review()->exists();
    }
}
