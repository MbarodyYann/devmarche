<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use App\Models\Mission;
use App\Services\MissionService;
use Illuminate\Http\RedirectResponse;

class MissionApplicationController extends Controller
{
    public function store(StoreApplicationRequest $request, Mission $mission, MissionService $missions): RedirectResponse
    {
        $missions->apply($mission, $request->user(), $request->validated());

        return redirect()
            ->route('missions.show', $mission)
            ->with('status', 'Candidature envoyée. Le client choisira parmi les candidats ; vous serez prévenu de sa décision.');
    }
}
