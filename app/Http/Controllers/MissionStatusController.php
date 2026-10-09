<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Services\MissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class MissionStatusController extends Controller
{
    public function complete(Mission $mission, MissionService $missions): RedirectResponse
    {
        Gate::authorize('complete', $mission);

        $missions->complete($mission);

        return redirect()
            ->route('missions.show', $mission)
            ->with('status', 'Mission terminée. '.$mission->freelancer()->value('name').' reçoit +20 cauris de réputation.');
    }

    public function cancel(Mission $mission, MissionService $missions): RedirectResponse
    {
        Gate::authorize('cancel', $mission);

        $missions->cancel($mission);

        return redirect()
            ->route('missions.show', $mission)
            ->with('status', 'Mission annulée. Les candidatures en attente sont closes.');
    }
}
