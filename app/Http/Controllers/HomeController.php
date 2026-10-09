<?php

namespace App\Http\Controllers;

use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Models\Profile;
use App\Models\ReputationEvent;
use App\Models\User;
use App\Support\Countries;
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
                'missions' => Mission::query()->count(),
                // Cauris gagnés : la somme des gains (les pertes éventuelles ne sont pas soustraites).
                'cauris' => (int) ReputationEvent::query()->where('points', '>', 0)->sum('points'),
                'avatarIds' => User::query()->whereHas('profile')->orderByDesc('reputation')->limit(4)->pluck('id')->all(),
            ];
        });

        $openMissions = Mission::query()
            ->where('status', MissionStatus::Open)
            ->with(['skills', 'client.profile'])
            ->withCount('applications')
            ->latest()
            ->limit(3)
            ->get();

        return view('home', [
            'stats' => $stats,
            'avatars' => User::query()->with('profile')->findMany($stats['avatarIds'])->sortByDesc('reputation')->values(),
            'openMissions' => $openMissions,
        ]);
    }
}
