<?php

namespace App\Http\Controllers;

use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Models\Profile;
use App\Models\Question;
use App\Models\User;
use App\Support\Categories;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Durée de cache des chiffres de la preuve sociale (10 minutes). */
    public const STATS_TTL = 600;

    public function __invoke(): View
    {
        $stats = Cache::remember('home.stats', self::STATS_TTL, fn () => [
            'devs' => User::query()->whereHas('profile')->count(),
            'countries' => Profile::query()->distinct()->count('country'),
            'missions' => Mission::query()->count(),
        ]);

        $openMissions = Mission::query()
            ->where('status', MissionStatus::Open)
            ->with(['skills', 'client.profile'])
            ->withCount('applications')
            ->latest()
            ->limit(3)
            ->get();

        $resolvedQuestions = Question::query()
            ->whereNotNull('accepted_answer_id')
            ->with(['skills', 'user' => fn ($q) => $q->withLevel(), 'user.profile'])
            ->latest()
            ->limit(3)
            ->get();

        // Nombre réel de missions ouvertes par catégorie (non mis en cache : il doit être exact).
        $openByCategory = Categories::openMissionCounts();

        return view('home', [
            'categories' => collect(Categories::all())->map(fn (array $category, string $key) => $category + ['open' => $openByCategory[$key]])->all(),
            'resolvedQuestions' => $resolvedQuestions,
            'stats' => $stats,
            'openMissions' => $openMissions,
        ]);
    }
}
