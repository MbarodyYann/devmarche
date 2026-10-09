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
            ->with('status', 'Mission terminée. +20 cauris pour le freelance, bravo à tous les deux !');
    }

    public function cancel(Mission $mission, MissionService $missions): RedirectResponse
    {
        Gate::authorize('cancel', $mission);

        $missions->cancel($mission);

        return redirect()
            ->route('missions.show', $mission)
            ->with('status', 'Mission annulée.');
    }
}
