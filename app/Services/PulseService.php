<?php

namespace App\Services;

use App\Support\Countries;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * « Le Pouls » : statistiques réelles du marché, calculées en SQL et mises en cache 10 minutes.
 * Le budget médian sert aussi à l'estimateur de budget de /missions/publier.
 */
class PulseService
{
    public const TTL = 600;

    /** En dessous, une médiane ne veut rien dire : on n'affiche rien. */
    public const MIN_COMPARABLE = 3;

    /**
     * @return array{devsByCountry: list<array{country: string, name: string, flag: string, count: int}>, topSkills: list<array{name: string, count: int}>, medianBySkill: list<array{name: string, median: int, count: int}>, resolvedThisWeek: int, firstApplicationHours: float|null, missionsLast30: int}
     */
    public function snapshot(): array
    {
        return Cache::remember('pulse.snapshot', self::TTL, fn () => [
            'devsByCountry' => $this->devsByCountry(),
            'topSkills' => $this->topSkills(),
            'medianBySkill' => $this->medianBySkill(),
            'resolvedThisWeek' => $this->resolvedThisWeek(),
            'firstApplicationHours' => $this->firstApplicationHours(),
            'missionsLast30' => DB::table('missions')->whereNull('hidden_at')->where('created_at', '>=', now()->subDays(30))->count(),
        ]);
    }

    /**
     * @return list<array{country: string, name: string, flag: string, count: int}>
     */
    public function devsByCountry(): array
    {
        return DB::table('profiles')
            ->select('country', DB::raw('count(*) as total'))
            ->groupBy('country')
            ->orderByDesc('total')
            ->orderBy('country')
            ->get()
            ->map(fn (object $row) => [
                'country' => $row->country,
                'name' => Countries::all()[$row->country] ?? $row->country,
                'flag' => Countries::flag($row->country),
                'count' => (int) $row->total,
            ])->all();
    }

    /**
     * Compétences les plus demandées dans les missions des 30 derniers jours.
     *
     * @return list<array{name: string, count: int}>
     */
    public function topSkills(int $limit = 8): array
    {
        return DB::table('mission_skill')
            ->join('missions', 'missions.id', '=', 'mission_skill.mission_id')
            ->join('skills', 'skills.id', '=', 'mission_skill.skill_id')
            ->whereNull('missions.hidden_at')
            ->where('missions.created_at', '>=', now()->subDays(30))
            ->select('skills.name', DB::raw('count(*) as total'))
            ->groupBy('skills.name')
            ->orderByDesc('total')
            ->orderBy('skills.name')
            ->limit($limit)
            ->get()
            ->map(fn (object $row) => ['name' => $row->name, 'count' => (int) $row->total])
            ->all();
    }

    /**
     * Budget médian par compétence (au moins 3 missions).
     *
     * @return list<array{name: string, median: int, count: int}>
     */
    public function medianBySkill(int $limit = 8): array
    {
        return DB::table('mission_skill')
            ->join('missions', 'missions.id', '=', 'mission_skill.mission_id')
            ->join('skills', 'skills.id', '=', 'mission_skill.skill_id')
            ->whereNull('missions.hidden_at')
            ->select('skills.name', DB::raw('percentile_cont(0.5) within group (order by missions.budget_fcfa) as median'), DB::raw('count(*) as total'))
            ->groupBy('skills.name')
            ->havingRaw('count(*) >= ?', [self::MIN_COMPARABLE])
            ->orderByDesc('median')
            ->limit($limit)
            ->get()
            ->map(fn (object $row) => ['name' => $row->name, 'median' => (int) round($row->median, -3), 'count' => (int) $row->total])
            ->all();
    }

    /**
     * Budget médian des missions qui demandent au moins une de ces compétences (et du même pays si donné).
     * Null sous 3 missions comparables. Utilisé par l'estimateur de budget (non mis en cache : saisie en direct).
     *
     * @param  list<string>  $skillNames
     * @return array{median: int, count: int}|null
     */
    public function medianFor(array $skillNames, ?string $country = null): ?array
    {
        if ($skillNames === []) {
            return null;
        }

        $row = DB::table('missions')
            ->whereNull('hidden_at')
            ->whereExists(fn ($q) => $q->select(DB::raw(1))
                ->from('mission_skill')
                ->join('skills', 'skills.id', '=', 'mission_skill.skill_id')
                ->whereColumn('mission_skill.mission_id', 'missions.id')
                ->whereIn(DB::raw('lower(skills.name)'), array_map('mb_strtolower', $skillNames)))
            ->when($country, fn ($q) => $q->where('country', $country))
            ->selectRaw('percentile_cont(0.5) within group (order by budget_fcfa) as median, count(*) as total')
            ->first();

        return $row && $row->total >= self::MIN_COMPARABLE
            ? ['median' => (int) round($row->median, -3), 'count' => (int) $row->total]
            : null;
    }

    /** Questions résolues ces 7 derniers jours (réponse acceptée). */
    public function resolvedThisWeek(): int
    {
        return DB::table('reputation_events')
            ->where('reason', 'answer_accepted')
            ->where('created_at', '>=', now()->subDays(7))
            ->distinct()
            ->count('source_id');
    }

    /** Délai moyen, en heures, entre la publication d'une mission et sa 1re candidature. */
    public function firstApplicationHours(): ?float
    {
        $hours = DB::table('missions')
            ->whereNull('missions.hidden_at')
            ->joinSub(
                DB::table('applications')->select('mission_id', DB::raw('min(created_at) as first_at'))->groupBy('mission_id'),
                'first',
                'first.mission_id',
                '=',
                'missions.id',
            )
            ->value(DB::raw('avg(extract(epoch from (first.first_at - missions.created_at)) / 3600)'));

        return $hours === null ? null : round((float) $hours, 1);
    }
}
