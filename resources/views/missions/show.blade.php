<x-layouts.app :title="$mission->title" :description="Str::limit($mission->description, 150)">
    @php
        $user = auth()->user();
        $isClient = $user && $user->id === $mission->client_id;
    @endphp

    <a href="{{ route('missions.index') }}" class="mb-4 inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-ink">
        <x-icon name="arrow-left" class="size-4" /> Toutes les missions
    </a>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        {{-- Contenu --}}
        <div class="min-w-0 space-y-6">
            <x-card>
                <div class="flex flex-wrap items-center gap-2">
                    <x-chip :tone="$mission->status->tone()" :dot="$mission->status->tone() !== 'done'">{{ $mission->status->label() }}</x-chip>
                    <x-chip tone="neutral">
                        @if ($mission->isRemote())
                            Télétravail
                        @else
                            <x-icon name="map-pin" class="size-3.5" /> {{ $mission->locationLabel() }} <span aria-hidden="true">{{ $mission->flag() }}</span>
                        @endif
                    </x-chip>
                    <span class="text-sm text-ink-muted">Publiée le {{ $mission->created_at->translatedFormat('j F Y') }}</span>
                </div>
                <h1 class="mt-4 text-[34px] sm:text-[44px]">{{ $mission->title }}</h1>
                <div class="mt-5 max-w-prose leading-relaxed text-ink-soft">{!! nl2br(e($mission->description)) !!}</div>
                @if ($mission->skills->isNotEmpty())
                    <div class="mt-6 border-t border-line pt-5">
                        <h2 class="mb-3 font-sans text-sm font-semibold tracking-normal text-ink">Compétences recherchées</h2>
                        <x-skills :skills="$mission->skills" />
                    </div>
                @endif
            </x-card>

            {{-- Candidatures : vue du client --}}
            @if ($isClient)
                <x-card>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-[28px]">Candidatures <span class="text-ink-muted">({{ $applications->count() }})</span></h2>
                        @can('cancel', $mission)
                            <form method="post" action="{{ route('missions.cancel', $mission) }}">
                                @csrf
                                <x-button type="submit" variant="danger" size="sm">Annuler la mission</x-button>
                            </form>
                        @endcan
                    </div>

                    @can('complete', $mission)
                        <div class="mt-5 flex flex-col gap-3 rounded-2xl bg-bg-alt p-4 ring-1 ring-line sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-ink-soft">Le travail est livré ? Clôturez la mission : {{ $mission->freelancer->name }} gagnera 20 cauris.</p>
                            <form method="post" action="{{ route('missions.complete', $mission) }}">
                                @csrf
                                <x-button type="submit" class="w-full sm:w-auto"><x-icon name="check" class="size-4" /> Marquer terminée</x-button>
                            </form>
                        </div>
                    @endcan

                    @if ($applications->isEmpty())
                        <p class="mt-5 text-ink-muted">Pas encore de candidature. Partagez la mission pour la faire connaître.</p>
                    @else
                        <ul class="mt-5 divide-y divide-line">
                            @foreach ($applications as $application)
                                <li class="flex flex-col gap-3 py-5 first:pt-0 last:pb-0">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <a href="{{ route('devs.show', $application->user) }}" class="flex min-w-0 items-center gap-3">
                                            <x-avatar :user="$application->user" size="sm" />
                                            <span class="min-w-0">
                                                <span class="block truncate font-semibold text-ink">{{ $application->user->name }}</span>
                                                <span class="block truncate text-sm text-ink-muted">{{ $application->user->profile?->headline }}</span>
                                            </span>
                                        </a>
                                        <x-reputation :points="$application->user->reputation" />
                                        <span class="ml-auto font-serif text-xl font-medium text-ink">{{ App\Support\Money::fcfa($application->proposed_fcfa) }} <span class="font-sans text-sm text-ink-muted">FCFA</span></span>
                                    </div>
                                    <p class="whitespace-pre-line text-ink-soft">{{ $application->message }}</p>
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <x-chip :tone="$application->status->tone()">{{ $application->status->label() }}</x-chip>
                                        @can('accept', [$mission, $application])
                                            <form method="post" action="{{ route('missions.accept', [$mission, $application]) }}">
                                                @csrf
                                                <x-button type="submit" size="sm">Retenir</x-button>
                                            </form>
                                        @endcan
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>
            @endif
        </div>

        {{-- Colonne latérale : budget dans le cadre repères, client, candidature --}}
        <aside class="space-y-6">
            <x-frame class="bg-surface p-6 sm:p-7">
                <x-budget :amount="$mission->budget_fcfa" size="lg" />
                <dl class="mt-6 grid grid-cols-2 divide-x divide-line border-t border-line pt-4 text-sm">
                    <div class="pr-4">
                        <dt class="text-ink-muted">Candidatures</dt>
                        <dd class="mt-1 font-serif text-2xl font-medium text-ink">{{ $mission->applications_count }}</dd>
                    </div>
                    <div class="pl-4">
                        <dt class="text-ink-muted">Échéance</dt>
                        <dd class="mt-1 font-serif text-2xl font-medium text-ink">{{ $mission->deadline?->translatedFormat('j M') ?? 'Libre' }}</dd>
                    </div>
                </dl>
            </x-frame>

            <x-card padding="p-6">
                <h2 class="font-sans text-sm font-semibold tracking-normal text-ink-muted">Publiée par</h2>
                <a href="{{ route('devs.show', $mission->client) }}" class="mt-3 flex items-center gap-3">
                    <x-avatar :user="$mission->client" />
                    <span class="min-w-0">
                        <span class="block truncate font-semibold text-ink">{{ $mission->client->name }}</span>
                        <span class="block truncate text-sm text-ink-muted">{{ $mission->client->profile?->headline }}</span>
                    </span>
                </a>
                <x-reputation :points="$mission->client->reputation" class="mt-3" />

                @if ($mission->freelancer)
                    <h2 class="mt-6 border-t border-line pt-5 font-sans text-sm font-semibold tracking-normal text-ink-muted">Confiée à</h2>
                    <a href="{{ route('devs.show', $mission->freelancer) }}" class="mt-3 flex items-center gap-3">
                        <x-avatar :user="$mission->freelancer" />
                        <span class="block truncate font-semibold text-ink">{{ $mission->freelancer->name }}</span>
                    </a>
                @endif
            </x-card>

            @unless ($isClient)
                <x-card padding="p-6" id="postuler">
                    @if ($myApplication)
                        <h2 class="text-2xl">Votre candidature</h2>
                        <p class="mt-3 text-ink-soft">Vous avez postulé le {{ $myApplication->created_at->translatedFormat('j M') }} pour {{ App\Support\Money::fcfa($myApplication->proposed_fcfa) }} FCFA.</p>
                        <x-chip :tone="$myApplication->status->tone()" class="mt-4">{{ $myApplication->status->label() }}</x-chip>
                    @elseif (! $mission->isOpen())
                        <h2 class="text-2xl">Candidatures closes</h2>
                        <p class="mt-3 text-ink-soft">Cette mission n'accepte plus de candidatures.</p>
                    @elseif (! $user)
                        <h2 class="text-2xl">Cette mission vous intéresse ?</h2>
                        <p class="mt-3 text-ink-soft">Connectez-vous pour postuler en deux minutes.</p>
                        <div class="mt-5 flex flex-col gap-3">
                            <x-button :href="route('login')">Se connecter pour postuler</x-button>
                            <x-button :href="route('register')" variant="secondary">{{ App\Support\Copy::SIGNUP_CTA }}</x-button>
                        </div>
                    @else
                        <h2 class="text-2xl">Postuler</h2>
                        <form method="post" action="{{ route('missions.apply', $mission) }}" class="mt-5 space-y-5" novalidate>
                            @csrf
                            <x-field label="Votre message" name="message" hint="Votre expérience utile, votre approche, vos disponibilités.">
                                <x-textarea name="message" rows="5" maxlength="2000" required />
                            </x-field>
                            <x-field label="Prix proposé (FCFA)" name="proposed_fcfa">
                                <x-input name="proposed_fcfa" inputmode="numeric" :value="App\Support\Money::fcfa($mission->budget_fcfa)" required />
                            </x-field>
                            <x-button type="submit" class="w-full">Envoyer ma candidature</x-button>
                        </form>
                    @endif
                </x-card>
            @endunless
        </aside>
    </div>
</x-layouts.app>
