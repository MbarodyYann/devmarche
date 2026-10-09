<x-layouts.app :title="$dev->name" :description="collect([$dev->profile?->headline, $dev->profile?->bio])->filter()->join('. ') ?: $dev->name.', dev sur DevMarché'">
    @php($profile = $dev->profile)

    @php($isOwner = auth()->id() === $dev->id)

    {{-- Encart d'action pour le propriétaire : bienvenue après l'inscription, ou profil sans compétences --}}
    @if ($isOwner && (session('welcome') || $dev->skills->isEmpty()))
        <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-secondary-400 bg-secondary-50 p-5 sm:flex-row sm:items-center sm:justify-between dark:bg-secondary-950" role="status">
            <p class="text-ink">
                @if (session('welcome'))
                    <strong class="font-semibold">Bienvenue !</strong> Ajoutez vos compétences pour recevoir les missions qui vous correspondent.
                @else
                    Les clients filtrent d'abord par compétence. Ajoutez les vôtres pour apparaître dans leurs recherches.
                @endif
            </p>
            <x-button :href="route('profile.edit', $dev)" class="shrink-0">Compléter mon profil</x-button>
        </div>
    @endif

    {{-- En-tête : identité à gauche, compteur de cauris dans le cadre repères à droite --}}
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-stretch">
        <x-card class="flex min-w-0 flex-col gap-6 sm:flex-row sm:items-start">
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
                        <x-chip :tone="$profile->availability->tone()">{{ $profile->availability->label() }}</x-chip>
                        <x-chip tone="neutral"><x-icon name="map-pin" class="size-3.5" /> {{ $profile->city }}, {{ $profile->countryName() }} <span aria-hidden="true">{{ $profile->flag() }}</span></x-chip>
                    @endif
                    <x-level :user="$dev" />
                </div>
                @if ($profile?->languagesLabel() || $dev->activityLabel())
                    <p class="mt-3 text-sm text-ink-muted">{{ collect([$profile?->languagesLabel(), $dev->activityLabel()])->filter()->join(' · ') }}</p>
                @endif
                <x-share class="mt-5" :title="collect([$dev->name, $profile?->headline])->filter()->join(', ')" :url="route('devs.show', $dev)" />
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

        <x-frame class="flex min-w-0 flex-col justify-between gap-6 bg-surface p-6 sm:p-8">
            <x-reputation :points="$dev->reputation" size="lg" />
            <dl class="grid grid-cols-2 divide-x divide-line border-t border-line pt-4 text-sm">
                <div class="pr-4">
                    <dt class="text-ink-muted">Missions réalisées</dt>
                    <dd class="mt-1 font-serif text-2xl font-medium text-ink">{{ $completedCount }}</dd>
                </div>
                <div class="pl-4">
                    <dt class="text-ink-muted">Membre depuis</dt>
                    <dd class="mt-1 font-serif text-2xl font-medium text-ink">{{ $dev->created_at->translatedFormat('M Y') }}</dd>
                </div>
            </dl>
        </x-frame>
    </section>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="min-w-0 space-y-6">
            <x-card>
                <h2 class="text-[28px] sm:text-[32px]">À propos</h2>
                @if ($profile?->bio)
                    <p class="mt-4 max-w-prose whitespace-pre-line leading-relaxed">{{ $profile->bio }}</p>
                @else
                    <p class="mt-4 text-ink-muted">{{ $dev->name }} n'a pas encore écrit de présentation.</p>
                @endif
            </x-card>

            <x-card>
                <h2 class="text-[28px] sm:text-[32px]">Réponses <span class="text-ink-muted">({{ $answersCount }})</span></h2>
                @if ($answers->isEmpty())
                    <p class="mt-4 text-ink-muted">
                        {{ $isOwner ? 'Vos réponses aux questions de la communauté apparaîtront ici. Chaque vote positif rapporte +10 cauris.' : 'Aucune réponse pour l\'instant.' }}
                    </p>
                @else
                    <ul class="mt-4 divide-y divide-line">
                        @foreach ($answers as $answer)
                            <li class="flex items-start gap-3 py-3">
                                <span class="w-10 shrink-0 rounded-[6px] bg-bg-alt py-1 text-center text-sm font-semibold tabular-nums text-ink ring-1 ring-line">{{ $answer->score }}</span>
                                <a href="{{ route('questions.show', $answer->question).'#reponse-'.$answer->id }}" class="min-w-0 flex-1 font-medium text-ink hover:text-primary-600 dark:hover:text-primary-300">{{ $answer->question->title }}</a>
                                @if ($answer->question->accepted_answer_id === $answer->id)
                                    <x-chip tone="done" class="shrink-0">Acceptée</x-chip>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>

            @if ($questions->isNotEmpty())
                <x-card>
                    <h2 class="text-[28px] sm:text-[32px]">Questions</h2>
                    <ul class="mt-4 divide-y divide-line">
                        @foreach ($questions as $question)
                            <li class="flex items-start gap-3 py-3">
                                <a href="{{ route('questions.show', $question) }}" class="min-w-0 flex-1 font-medium text-ink hover:text-primary-600 dark:hover:text-primary-300">{{ $question->title }}</a>
                                <span class="shrink-0 text-sm text-ink-muted">{{ $question->answers_count }} {{ $question->answers_count > 1 ? 'réponses' : 'réponse' }}</span>
                                @if ($question->accepted_answer_id)
                                    <x-chip tone="done" class="shrink-0">Résolue</x-chip>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            @endif

            <section>
                <h2 class="mb-4 text-[28px] sm:text-[32px]">Missions réalisées</h2>
                @if ($completedMissions->isEmpty())
                    <x-card padding="p-6">
                        @if ($isOwner)
                            <p class="text-ink-soft">Votre première mission terminée apparaîtra ici, avec +20 cauris.</p>
                            <x-button :href="route('missions.index', ['pour_moi' => 1])" variant="secondary" class="mt-4">{{ App\Support\Copy::MEMBER_CTA }}</x-button>
                        @else
                            <p class="text-ink-muted">Aucune mission terminée pour l'instant.</p>
                        @endif
                    </x-card>
                @else
                    <ul class="grid gap-4 sm:grid-cols-2">
                        @foreach ($completedMissions as $mission)
                            <li class="min-w-0"><x-mission-card :mission="$mission" /></li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <aside class="min-w-0 space-y-6">
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
                                   class="flex min-h-11 items-center gap-2 font-medium text-ink hover:text-primary-600 dark:hover:text-primary-300">
                                    <x-icon name="github" class="size-5" /> GitHub <x-icon name="arrow-right" class="ml-auto size-4 text-ink-muted" />
                                </a>
                            </li>
                        @endif
                        @if ($profile->portfolio_url)
                            <li>
                                <a href="{{ $profile->portfolio_url }}" rel="nofollow ugc noopener" target="_blank"
                                   class="flex min-h-11 items-center gap-2 font-medium text-ink hover:text-primary-600 dark:hover:text-primary-300">
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
