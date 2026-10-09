<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Http\Requests\StoreMissionRequest;
use App\Models\Mission;
use App\Models\Skill;
use App\Services\MissionService;
use App\Support\Countries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MissionController extends Controller
{
    public function create(Request $request): View
    {
        Gate::authorize('create', Mission::class);

        return view('missions.create', [
            'countries' => Countries::grouped(),
            'defaultCountry' => $request->user()->profile?->country,
            'suggestedSkills' => Skill::query()->orderBy('name')->pluck('name'),
        ]);
    }

    public function store(StoreMissionRequest $request, MissionService $missions): RedirectResponse
    {
        $mission = $missions->publish($request->user(), $request->missionData(), $request->skillNames());

        return redirect()
            ->route('missions.show', $mission)
            ->with('status', 'Mission publiée. Les devs arrivent : vous verrez leurs candidatures ici.');
    }

    public function show(Request $request, Mission $mission): View
    {
        $mission->load(['client.profile', 'freelancer.profile', 'skills'])->loadCount('applications');
        $user = $request->user();

        return view('missions.show', [
            'mission' => $mission,
            'myApplication' => $user ? $mission->applications()->where('user_id', $user->id)->first() : null,
            'applications' => $user?->can('viewApplications', $mission)
                ? $mission->applications()
                    ->with('user.profile')
                    ->orderByRaw('case status when ? then 0 when ? then 1 else 2 end', [ApplicationStatus::Accepted->value, ApplicationStatus::Pending->value])
                    ->latest()
                    ->get()
                : collect(),
        ]);
    }
}
