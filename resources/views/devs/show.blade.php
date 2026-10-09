<x-layouts.app :title="$dev->name">
    @php($profile = $dev->profile)

    {{-- En-tête : identité à gauche, compteur de cauris dans le cadre repères à droite --}}
    <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-stretch">
        <x-card class="flex flex-col gap-6 sm:flex-row sm:items-start">
            <x-avatar :user="$dev" size="lg" class="shadow-card" />
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="eyebrow mb-2">Profil de dev</p>
                        <h1 class="text-[40px] sm:text-5xl">{{ $dev->name }}</h1>
                    </div>
                    @can('update', $profile)
                        <div class="hidden sm:block">
                            <x-button :href="route('profile.edit', $dev)" variant="secondary" size="sm">
                                <x-icon name="pencil" class="size-4" /> Modifier mon profil
                            </x-button>
                        </div>
                    @endcan
                </div>
                @if ($profile?->headline)
                    <p class="mt-3 text-lg text-ink-soft">{{ $profile->headline }}</p>
                @endif
                <div class="mt-5 flex flex-wrap items-center gap-2">
                    @if ($profile)
                        <x-chip :tone="$profile->availability->tone()" dot>{{ $profile->availability->label() }}</x-chip>
                        <x-chip tone="neutral"><x-icon name="map-pin" class="size-3.5" /> {{ $profile->city }}, {{ $profile->countryName() }} <span aria-hidden="true">{{ $profile->flag() }}</span></x-chip>
                    @endif
                    @if ($dev->isNewMember())
                        <x-new-badge />
                    @endif
                </div>
                @can('update', $profile)
                    {{-- Sur mobile, le bouton passe sous les puces, en pleine largeur. --}}
                    <div class="mt-5 sm:hidden">
                        <x-button :href="route('profile.edit', $dev)" variant="secondary" class="w-full">
                            <x-icon name="pencil" class="size-4" /> Modifier mon profil
                        </x-button>
                    </div>
                @endcan
            </div>
        </x-card>

        <x-frame class="flex flex-col justify-between gap-6 bg-surface p-6 sm:p-8">
            <x-reputation :points="$dev->reputation" size="lg" />
            <dl class="grid grid-cols-2 divide-x divide-line border-t border-line pt-4 text-sm">
                <div class="pr-4">
                    <dt class="text-ink-muted">Missions réalisées</dt>
                    <dd class="mt-1 font-serif text-2xl font-medium text-ink">0</dd>
                </div>
                <div class="pl-4">
                    <dt class="text-ink-muted">Membre depuis</dt>
                    <dd class="mt-1 font-serif text-2xl font-medium text-ink">{{ $dev->created_at->translatedFormat('M Y') }}</dd>
                </div>
            </dl>
        </x-frame>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <x-card>
                <h2 class="text-[28px] sm:text-[32px]">À propos</h2>
                @if ($profile?->bio)
                    <p class="mt-4 max-w-prose whitespace-pre-line leading-relaxed">{{ $profile->bio }}</p>
                @else
                    <p class="mt-4 text-ink-muted">{{ $dev->name }} n'a pas encore écrit de présentation.</p>
                @endif
            </x-card>

            <x-card>
                <h2 class="text-[28px] sm:text-[32px]">Missions réalisées</h2>
                <p class="mt-4 text-ink-muted">Aucune mission terminée pour l'instant.</p>
            </x-card>
        </div>

        <aside class="space-y-6">
            <x-card padding="p-6">
                <h2 class="text-2xl">Compétences</h2>
                @if ($dev->skills->isNotEmpty())
                    <x-skills :skills="$dev->skills" class="mt-4" />
                @else
                    <p class="mt-4 text-ink-muted">Aucune compétence renseignée.</p>
                @endif
            </x-card>

            @if ($profile?->github_url || $profile?->portfolio_url)
                <x-card padding="p-6">
                    <h2 class="text-2xl">Liens</h2>
                    <ul class="mt-3 divide-y divide-line">
                        @if ($profile->github_url)
                            <li>
                                <a href="{{ $profile->github_url }}" rel="nofollow ugc noopener" target="_blank"
                                   class="flex min-h-11 items-center gap-2 font-medium text-ink hover:text-primary-600 dark:hover:text-primary-400">
                                    <x-icon name="github" class="size-5" /> GitHub <x-icon name="arrow-right" class="ml-auto size-4 text-ink-muted" />
                                </a>
                            </li>
                        @endif
                        @if ($profile->portfolio_url)
                            <li>
                                <a href="{{ $profile->portfolio_url }}" rel="nofollow ugc noopener" target="_blank"
                                   class="flex min-h-11 items-center gap-2 font-medium text-ink hover:text-primary-600 dark:hover:text-primary-400">
                                    <x-icon name="link" class="size-5" /> Portfolio <x-icon name="arrow-right" class="ml-auto size-4 text-ink-muted" />
                                </a>
                            </li>
                        @endif
                    </ul>
                </x-card>
            @endif
        </aside>
    </div>
</x-layouts.app>
