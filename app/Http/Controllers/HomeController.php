<?php

namespace App\Http\Controllers;

use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Models\Profile;
use App\Models\User;
use App\Support\Countries;
use Database\Seeders\DevSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Durée de cache des chiffres de la preuve sociale (10 minutes). */
    public const STATS_TTL = 600;

    public function __invoke(): View
    {
        $stats = Cache::remember('home.stats', self::STATS_TTL, function () {
            $countries = Profile::query()->distinct()->pluck('country')->all();

            return [
                'devs' => User::query()->whereHas('profile')->count(),
                'countries' => count($countries),
                // Drapeaux dans l'ordre de config/countries.php (Afrique francophone d'abord).
                'flags' => array_values(array_map(Countries::flag(...), array_keys(array_intersect_key(Countries::all(), array_flip($countries))))),
                'completedMissions' => Mission::query()->where('status', MissionStatus::Completed)->count(),
                'avatarIds' => User::query()->whereHas('profile')->orderByDesc('reputation')->limit(5)->pluck('id')->all(),
            ];
        });

        $openMissions = Mission::query()
            ->where('status', MissionStatus::Open)
            ->with(['skills', 'client.profile'])
            ->withCount('applications')
            ->latest()
            ->limit(3)
            ->get();

        // Maquette : la mission Wave d'Aïssatou et Moussa (comptes de démo), sinon des données réelles équivalentes.
        $mockMission = Mission::query()
            ->where('status', MissionStatus::Open)
            ->whereHas('client', fn ($q) => $q->where('email', DevSeeder::DEMO_STARTUP_EMAIL))
            ->where('title', 'like', '%Wave%')
            ->with(['skills', 'client.profile'])
            ->withCount('applications')
            ->first() ?? $openMissions->first();

        $mockDev = User::query()->where('email', DevSeeder::DEMO_DEV_EMAIL)->with('profile')->first()
            ?? User::query()->whereHas('profile')->with('profile')->orderByDesc('reputation')->first();

        return view('home', [
            'stats' => $stats,
            'avatars' => User::query()->with('profile')->findMany($stats['avatarIds'])->sortByDesc('reputation')->values(),
            'openMissions' => $openMissions,
            'mockMission' => $mockMission,
            'mockDev' => $mockDev,
        ]);
    }
}
