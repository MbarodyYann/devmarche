<x-layouts.app :title="$dev->name">
    @php($profile = $dev->profile)

    {{-- En-tête : bannière frise, avatar carré, nom en Danfo, compteur de cauris --}}
    <section class="rounded-block border-2 border-ink bg-surface shadow-hard">
        <div class="relative h-24 overflow-hidden border-b-2 border-ink bg-bg sm:h-28">
            <div class="frise absolute inset-0 opacity-[0.12]" aria-hidden="true"></div>
        </div>

        <div class="grid gap-6 px-5 pb-6 sm:px-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
            <div>
                <div class="relative -mt-14 flex items-end justify-between gap-4 sm:-mt-16">
                    <x-avatar :user="$dev" size="lg" class="shadow-hard" />
                    @can('update', $profile)
                        <x-button :href="route('profile.edit', $dev)" variant="secondary" size="sm">
                            <x-icon name="pencil" class="size-4" /> Modifier<span class="hidden sm:inline">&nbsp;mon profil</span>
                        </x-button>
                    @endcan
                </div>

                <h1 class="mt-5 text-3xl sm:text-4xl">{{ $dev->name }}</h1>
                @if ($profile?->headline)
                    <p class="mt-2 text-lg text-ink-muted">{{ $profile->headline }}</p>
                @endif

                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-3">
                    @if ($profile)
                        <x-stamp :tone="$profile->availability->tone()">{{ $profile->availability->stamp() }}</x-stamp>
                    @endif
                    @if ($dev->isNewMember())
                        <x-new-badge />
                    @endif
                    @if ($profile)
                        <span class="etiquette inline-flex items-center gap-1.5 text-ink-muted">
                            <span aria-hidden="true">{{ $profile->flag() }}</span>
                            {{ $profile->city }} · {{ $profile->countryName() }}
                        </span>
                    @endif
                </div>
            </div>

            <x-reputation :points="$dev->reputation" size="lg" class="lg:pb-1" />
        </div>
    </section>

    {{-- Grille asymétrique : 8 colonnes de contenu, 4 de colonne latérale --}}
    <div class="mt-10 grid gap-10 lg:grid-cols-12">
        <div class="space-y-10 lg:col-span-8">
            <section class="filet pt-4">
                <h2 class="text-2xl">À propos</h2>
                @if ($profile?->bio)
                    <p class="mt-3 max-w-prose whitespace-pre-line leading-relaxed">{{ $profile->bio }}</p>
                @else
                    <p class="mt-3 text-ink-muted">{{ $dev->name }} n'a pas encore écrit de présentation.</p>
                @endif
            </section>

            <section class="filet pt-4">
                <h2 class="text-2xl">Missions réalisées</h2>
                <p class="mt-3 text-ink-muted">Aucune mission terminée pour l'instant.</p>
            </section>
        </div>

        <aside class="space-y-10 lg:col-span-4">
            <section class="filet pt-4">
                <h2 class="text-2xl">Compétences</h2>
                @if ($dev->skills->isNotEmpty())
                    <ul class="mt-3 divide-y divide-border border-b border-border">
                        @foreach ($dev->skills as $skill)
                            <li>
                                <a href="{{ route('devs.index', ['competence' => $skill->slug]) }}"
                                   class="etiquette flex min-h-11 items-center justify-between gap-3 text-ink decoration-primary decoration-2 underline-offset-3 hover:underline">
                                    {{ $skill->name }}
                                    <span aria-hidden="true" class="text-ink-muted">→</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-3 text-ink-muted">Aucune compétence renseignée.</p>
                @endif
            </section>

            @if ($profile?->github_url || $profile?->portfolio_url)
                <section class="filet pt-4">
                    <h2 class="text-2xl">Liens</h2>
                    <ul class="mt-3 space-y-1">
                        @if ($profile->github_url)
                            <li>
                                <a href="{{ $profile->github_url }}" rel="nofollow ugc noopener" target="_blank"
                                   class="inline-flex min-h-11 items-center gap-2 font-bold text-primary-strong underline decoration-2 underline-offset-4">
                                    <x-icon name="github" class="size-5" /> GitHub
                                </a>
                            </li>
                        @endif
                        @if ($profile->portfolio_url)
                            <li>
                                <a href="{{ $profile->portfolio_url }}" rel="nofollow ugc noopener" target="_blank"
                                   class="inline-flex min-h-11 items-center gap-2 font-bold text-primary-strong underline decoration-2 underline-offset-4">
                                    <x-icon name="link" class="size-5" /> Portfolio
                                </a>
                            </li>
                        @endif
                    </ul>
                </section>
            @endif

            <p class="etiquette text-ink-muted">Membre depuis {{ Str::upper($dev->created_at->translatedFormat('M Y')) }}</p>
        </aside>
    </div>
</x-layouts.app>
