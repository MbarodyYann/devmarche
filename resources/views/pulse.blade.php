@php
    $hours = $pulse['firstApplicationHours'];
    $firstApplication = match (true) {
        $hours === null => null,
        $hours < 1 => max(1, (int) round($hours * 60)).' min',
        $hours < 48 => floor($hours).' h'.(($minutes = (int) round(($hours - floor($hours)) * 60)) ? ' '.str_pad((string) $minutes, 2, '0', STR_PAD_LEFT) : ''),
        default => round($hours / 24).' jours',
    };
@endphp
<x-layouts.app title="Le Pouls" :page-hero="[
    'start' => 'Le marché des devs,',
    'end' => 'en chiffres réels.',
    'subtitle' => 'Calculés sur les missions, les profils et les questions de DevMarché, et mis à jour toutes les 10 minutes.',
]">
    {{-- Le Pouls (lot 6) : statistiques réelles, en barres CSS, sans bibliothèque JS. --}}
    <x-card padding="p-4 sm:p-6" class="relative z-10 -mt-6 sm:-mt-10">
        <dl class="grid grid-cols-1 gap-4 text-center sm:grid-cols-3 sm:divide-x sm:divide-line">
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[.14em] text-ink-muted">Missions publiées en 30 jours</dt>
                <dd class="mt-2 font-serif text-[32px] font-medium leading-none text-ink" data-pulse="missions">{{ $pulse['missionsLast30'] }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[.14em] text-ink-muted">Questions résolues cette semaine</dt>
                <dd class="mt-2 font-serif text-[32px] font-medium leading-none text-ink" data-pulse="resolved">{{ $pulse['resolvedThisWeek'] }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-[.14em] text-ink-muted">Délai moyen avant la 1re candidature</dt>
                <dd class="mt-2 font-serif text-[32px] font-medium leading-none text-ink" data-pulse="first-application">{{ $firstApplication ?? '—' }}</dd>
            </div>
        </dl>
    </x-card>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card>
            <h2 class="text-[26px] sm:text-[30px]">Les compétences les plus demandées</h2>
            <p class="mt-1 text-sm text-ink-muted">Nombre de missions publiées ces 30 derniers jours qui demandent la compétence.</p>
            @if ($pulse['topSkills'])
                <x-bar-list class="mt-5" :rows="$pulse['topSkills']" label="name" value="count" data-pulse-list="skills" />
            @else
                <p class="mt-5 text-ink-muted">Pas encore assez de missions ce mois-ci.</p>
            @endif
        </x-card>

        <x-card>
            <h2 class="text-[26px] sm:text-[30px]">Budget médian par compétence</h2>
            <p class="mt-1 text-sm text-ink-muted">Compétences demandées dans au moins {{ App\Services\PulseService::MIN_COMPARABLE }} missions.</p>
            @if ($pulse['medianBySkill'])
                <x-bar-list class="mt-5" :rows="$pulse['medianBySkill']" label="name" value="median" data-pulse-list="median"
                            :format="fn ($row) => App\Support\Money::fcfa($row['median']).' FCFA'" />
            @else
                <p class="mt-5 text-ink-muted">Pas encore assez de missions comparables.</p>
            @endif
        </x-card>

        <x-card class="lg:col-span-2">
            <h2 class="text-[26px] sm:text-[30px]">Les devs, pays par pays</h2>
            <p class="mt-1 text-sm text-ink-muted">Profils inscrits sur DevMarché.</p>
            <x-bar-list class="mt-5" :rows="collect($pulse['devsByCountry'])->map(fn ($row) => $row + ['label' => $row['flag'].' '.$row['name']])->all()" label="label" value="count" data-pulse-list="countries" />
        </x-card>
    </div>
</x-layouts.app>
