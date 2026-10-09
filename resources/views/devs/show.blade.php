<x-layouts.app :title="$dev->name">
    @php($profile = $dev->profile)

    <section class="relative overflow-hidden rounded-card border border-border bg-surface shadow-card">
        <div class="relative h-24 bg-secondary-soft sm:h-32">
            <div class="frise absolute inset-0 opacity-[0.12]" aria-hidden="true"></div>
        </div>

        <div class="px-5 pb-6 sm:px-8">
            <div class="relative -mt-12 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <x-avatar :user="$dev" size="lg" class="ring-4 ring-surface" />
                @can('update', $profile)
                    <x-button :href="route('profile.edit', $dev)" variant="secondary" size="sm" class="self-start sm:self-auto">
                        <x-icon name="pencil" class="size-4" /> Modifier mon profil
                    </x-button>
                @endcan
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-2">
                <h1 class="text-2xl sm:text-3xl">{{ $dev->name }}</h1>
                @if ($dev->isNewMember())
                    <x-new-badge />
                @endif
            </div>
            @if ($profile?->headline)
                <p class="mt-1 text-lg text-ink-muted">{{ $profile->headline }}</p>
            @endif

            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2">
                <x-reputation :points="$dev->reputation" size="lg" />
                @if ($profile)
                    <span class="inline-flex items-center gap-1.5 text-ink-muted">
                        <x-icon name="map-pin" class="size-4" />
                        <span aria-hidden="true">{{ $profile->flag() }}</span>
                        {{ $profile->city }}, {{ $profile->countryName() }}
                    </span>
                    <x-status-pill :tone="$profile->availability->tone()">{{ $profile->availability->label() }}</x-status-pill>
                @endif
            </div>
        </div>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card>
                <h2 class="text-xl">À propos</h2>
                @if ($profile?->bio)
                    <p class="mt-3 whitespace-pre-line leading-relaxed">{{ $profile->bio }}</p>
                @else
                    <p class="mt-3 text-ink-muted">{{ $dev->name }} n'a pas encore écrit de présentation.</p>
                @endif
            </x-card>

            <x-card>
                <h2 class="text-xl">Missions réalisées</h2>
                <p class="mt-3 text-ink-muted">Aucune mission terminée pour l'instant.</p>
            </x-card>
        </div>

        <aside class="space-y-6">
            <x-card>
                <h2 class="text-xl">Compétences</h2>
                @if ($dev->skills->isNotEmpty())
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($dev->skills as $skill)
                            <li><x-tag :href="route('devs.index', ['competence' => $skill->slug])">{{ $skill->name }}</x-tag></li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-3 text-ink-muted">Aucune compétence renseignée.</p>
                @endif
            </x-card>

            @if ($profile?->github_url || $profile?->portfolio_url)
                <x-card>
                    <h2 class="text-xl">Liens</h2>
                    <ul class="mt-3 space-y-2">
                        @if ($profile->github_url)
                            <li>
                                <a href="{{ $profile->github_url }}" rel="nofollow ugc noopener" target="_blank"
                                   class="inline-flex min-h-11 items-center gap-2 font-medium text-primary-strong underline-offset-4 hover:underline">
                                    <x-icon name="github" class="size-5" /> GitHub
                                </a>
                            </li>
                        @endif
                        @if ($profile->portfolio_url)
                            <li>
                                <a href="{{ $profile->portfolio_url }}" rel="nofollow ugc noopener" target="_blank"
                                   class="inline-flex min-h-11 items-center gap-2 font-medium text-primary-strong underline-offset-4 hover:underline">
                                    <x-icon name="link" class="size-5" /> Portfolio
                                </a>
                            </li>
                        @endif
                    </ul>
                </x-card>
            @endif

            <p class="text-sm text-ink-muted">Membre depuis {{ $dev->created_at->translatedFormat('F Y') }}</p>
        </aside>
    </div>
</x-layouts.app>
