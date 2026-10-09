<?php

namespace App\Http\Controllers;

use App\Enums\Availability;
use App\Enums\MissionStatus;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Review;
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
            ->with('client:id,name')
            ->latest('completed_at');

        $completedCount = (clone $completed)->count();
        $publishedCount = $user->missions()->count();

        // Avis reçus : moyenne, répartition 5 → 1, moyennes des 3 critères, puis la liste.
        $reviews = $user->receivedReviews()->with(['reviewer:id,name', 'mission:id,title,budget_fcfa,assigned_at,completed_at'])->latest()->get();
        $loyalClients = $user->assignedMissions()
            ->select('client_id')
            ->groupBy('client_id')
            ->havingRaw('count(*) >= 2')
            ->pluck('client_id');

        return view('devs.show', [
            'dev' => $user,
            'reviews' => $reviews,
            'reviewStats' => $reviews->isEmpty() ? null : [
                'average' => $reviews->avg('rating'),
                'count' => $reviews->count(),
                'distribution' => collect(range(5, 1))->mapWithKeys(fn (int $stars) => [$stars => $reviews->where('rating', $stars)->count()])->all(),
                'criteria' => collect(Review::CRITERIA)->map(fn (string $label, string $field) => $reviews->avg($field))->all(),
            ],
            'loyalClients' => $loyalClients,
            'completedCount' => $completedCount,
            // Statistiques du cadre repères : missions réalisées (dev), publiées (client), ancienneté.
            'stats' => collect([
                ['Missions réalisées', $completedCount, $completedCount > 0 || $publishedCount === 0],
                ['Missions publiées', $publishedCount, $publishedCount > 0],
                ['Membre depuis', $user->created_at->translatedFormat('M Y'), true],
            ])->filter(fn (array $stat) => $stat[2])->values(),
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
            // Missions publiées (côté client), ouvertes en tête.
            'publishedMissions' => $user->missions()
                ->with(['skills', 'client.profile'])
                ->withCount('applications')
                ->orderByRaw('status = ? desc', [MissionStatus::Open->value])
                ->latest()
                ->limit(6)
                ->get(),
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
