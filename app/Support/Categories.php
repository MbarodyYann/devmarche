<?php

namespace App\Support;

use App\Enums\MissionStatus;
use App\Models\Mission;
use Illuminate\Database\Eloquent\Builder;

/**
 * Catégories populaires (config/categories.php) : des groupes de compétences.
 */
class Categories
{
    /**
     * @return array<string, array{label: string, icon: string, skills: list<string>}>
     */
    public static function all(): array
    {
        return config('categories');
    }

    /**
     * @return array{label: string, icon: string, skills: list<string>}|null
     */
    public static function find(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /** Restreint une requête de missions à celles qui demandent au moins une compétence de la catégorie. */
    public static function scope(Builder $missions, string $key): Builder
    {
        return $missions->whereHas('skills', fn (Builder $skills) => $skills->whereIn('name', self::find($key)['skills'] ?? []));
    }

    /**
     * Nombre réel de missions ouvertes par catégorie.
     *
     * @return array<string, int>
     */
    public static function openMissionCounts(): array
    {
        return collect(self::all())
            ->map(fn (array $category, string $key) => self::scope(Mission::query()->where('status', MissionStatus::Open), $key)->count())
            ->all();
    }
}
