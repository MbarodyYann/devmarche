<?php

namespace App\Http\Controllers;

use App\Enums\Availability;
use App\Enums\MissionStatus;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Skill;
use App\Models\User;
use App\Services\AvatarService;
use App\Support\Countries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(User $user): View
    {
        $user->load(['profile', 'skills'])->loadCount(['assignedMissions as completed_missions_count' => fn ($q) => $q->where('status', MissionStatus::Completed)]);

        $completed = $user->assignedMissions()
            ->where('status', MissionStatus::Completed)
            ->with(['client.profile', 'skills'])
            ->withCount('applications')
            ->latest('completed_at');

        return view('devs.show', [
            'dev' => $user,
            'completedCount' => (clone $completed)->count(),
            'completedMissions' => $completed->limit(6)->get(),
            // Ses meilleures réponses d'abord (acceptées, puis par score).
            'answers' => $user->answers()
                ->with('question:id,title,accepted_answer_id')
                ->orderByDesc('score')
                ->latest()
                ->limit(5)
                ->get(),
            'answersCount' => $user->answers()->count(),
            'questions' => $user->questions()->latest()->limit(5)->get(['id', 'title', 'score', 'answers_count', 'accepted_answer_id', 'created_at']),
        ]);
    }

    public function edit(User $user): View
    {
        $user->load(['profile', 'skills']);
        Gate::authorize('update', $user->profile);

        return view('profile.edit', [
            'dev' => $user,
            'countries' => Countries::grouped(),
            'availabilities' => Availability::options(),
            'suggestedSkills' => Skill::query()->orderBy('name')->pluck('name'),
            'languageOptions' => config('languages'),
        ]);
    }

    public function update(UpdateProfileRequest $request, User $user, AvatarService $avatars): RedirectResponse
    {
        $profile = $user->profile;
        $data = $request->safe()->except(['name', 'avatar', 'skills', 'languages']);
        $data['languages'] = array_values(array_intersect(array_keys(config('languages')), $request->validated('languages') ?? []));

        if ($request->hasFile('avatar')) {
            $oldAvatar = $profile->avatar_path;
            $data['avatar_path'] = $avatars->store($request->file('avatar'));
        }

        DB::transaction(function () use ($request, $user, $profile, $data) {
            $user->update(['name' => $request->validated('name')]);
            $profile->update($data);
            $user->skills()->sync($this->skillIds($request->skillNames()));
        });

        if (isset($oldAvatar)) {
            $avatars->delete($oldAvatar);
        }

        return redirect()
            ->route('devs.show', $user)
            ->with('status', 'Profil mis à jour. Bonne visibilité !');
    }

    /**
     * Retrouve les compétences existantes (par slug) ou les crée.
     *
     * @param  list<string>  $names
     * @return list<int>
     */
    private function skillIds(array $names): array
    {
        return collect($names)
            ->map(fn (string $name) => Skill::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id)
            ->all();
    }
}
