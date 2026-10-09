<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Mission;
use App\Services\MissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class MissionApplicationController extends Controller
{
    public function store(StoreApplicationRequest $request, Mission $mission): RedirectResponse
    {
        $application = $mission->applications()->make($request->validated());
        $application->user()->associate($request->user());
        $application->save();

        return redirect()
            ->route('missions.show', $mission)
            ->with('status', 'Candidature envoyée. Bonne chance !');
    }

    public function accept(Mission $mission, Application $application, MissionService $missions): RedirectResponse
    {
        Gate::authorize('accept', [$mission, $application]);

        $missions->accept($mission, $application);

        return redirect()
            ->route('missions.show', $mission)
            ->with('status', 'Candidat retenu. La mission est en cours.');
    }
}
