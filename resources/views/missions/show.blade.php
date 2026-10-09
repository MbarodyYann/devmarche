<x-layouts.app :title="$mission->title" :description="$mission->description">
    @php
        $user = auth()->user();
        $isClient = $user && $user->id === $mission->client_id;
    @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <a href="{{ route('missions.index') }}" class="inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-ink">
            <x-icon name="arrow-left" class="size-4" /> Toutes les missions
        </a>
        <x-share :title="$mission->title.' · '.App\Support\Money::fcfa($mission->budget_fcfa).' FCFA'" :url="route('missions.show', $mission)" />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        {{-- Contenu --}}
        <div class="min-w-0 space-y-6">
            <x-card>
                <div class="flex flex-wrap items-center gap-2">
                    <x-chip :tone="$mission->status->tone()">{{ $mission->status->label() }}</x-chip>
                    <x-chip tone="neutral">
                        @if ($mission->isRemote())
                            Télétravail
                        @else
                            <x-icon name="map-pin" class="size-3.5" /> {{ $mission->locationLabel() }} <span aria-hidden="true">{{ $mission->flag() }}</span>
                        @endif
                    </x-chip>
                    <span class="text-sm text-ink-muted">Publiée le {{ $mission->created_at->translatedFormat('j F Y') }}</span>
                </div>
                <h1 class="mt-4 break-words text-[30px] sm:text-[44px]">{{ $mission->title }}</h1>
                {{-- HTML produit par MarkdownRenderer (HTML brut échappé, liens dangereux neutralisés). --}}
                <div class="prose-dm mt-5">{!! $mission->description_html !!}</div>
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
                    <h2 class="text-[28px]">Choisissez votre dev <span class="text-ink-muted">({{ $applications->count() }})</span></h2>
                    @if ($applications->whereNotNull('delivery_days')->isNotEmpty())
                        <p class="mt-1 text-sm text-ink-muted">Délai moyen proposé : <strong class="font-semibold text-ink">{{ (int) round($applications->avg('delivery_days')) }} jours</strong></p>
                    @endif
                    <p class="mt-2 text-ink-soft">Comparez les prix, les messages et les cauris. Quand vous retenez un dev, les autres candidatures passent automatiquement en « Non retenue ».</p>

                    @can('complete', $mission)
                        <div class="mt-5 flex flex-col gap-3 rounded-2xl bg-bg-alt p-4 ring-1 ring-line sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-ink">{{ $mission->freelancer->name }} travaille sur votre mission.</p>
                                <p class="text-sm text-ink-muted">Le dev reçoit +20 cauris de réputation.</p>
                            </div>
                            <form method="post" action="{{ route('missions.complete', $mission) }}">
                                @csrf
                                <x-button type="submit" class="w-full sm:w-auto"><x-icon name="check" class="size-4" /> Confirmer la fin de la mission</x-button>
                            </form>
                        </div>
                    @endcan

                    @if ($applications->isEmpty())
                        <p class="mt-5 text-ink-muted">Les candidatures arriveront ici. Partagez la mission pour la faire connaître.</p>
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
                                        <x-level :user="$application->user" />
                                        {{-- Prix et délai côte à côte : le client compare les deux. --}}
                                        <dl class="ml-auto flex items-end gap-5 text-right">
                                            <div>
                                                <dt class="text-xs text-ink-muted">Prix</dt>
                                                <dd class="font-serif text-xl font-medium text-ink">{{ App\Support\Money::fcfa($application->proposed_fcfa) }} <span class="font-sans text-sm text-ink-muted">FCFA</span></dd>
                                            </div>
                                            @if ($application->delivery_days)
                                                <div>
                                                    <dt class="text-xs text-ink-muted">Délai</dt>
                                                    <dd class="font-serif text-xl font-medium text-ink">{{ $application->delivery_days }} <span class="font-sans text-sm text-ink-muted">{{ $application->delivery_days > 1 ? 'jours' : 'jour' }}</span></dd>
                                                </div>
                                            @endif
                                        </dl>
                                    </div>
                                    @if ($activity = $application->user->activityLabel())
                                        <p class="text-sm text-ink-muted">{{ $activity }}</p>
                                    @endif
                                    <p class="whitespace-pre-line text-ink-soft">{{ $application->message }}</p>
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <x-chip :tone="$application->status->tone()">{{ $application->status->label() }}</x-chip>
                                        @can('accept', [$mission, $application])
                                            <form method="post" action="{{ route('missions.accept', [$mission, $application]) }}">
                                                @csrf
                                                <x-button type="submit" size="sm">Retenir ce dev</x-button>
                                            </form>
                                        @endcan
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @can('cancel', $mission)
                        <form method="post" action="{{ route('missions.cancel', $mission) }}" class="mt-6 border-t border-line pt-4 text-right">
                            @csrf
                            <button type="submit" class="text-sm text-ink-muted underline-offset-4 hover:text-ink hover:underline">Annuler la mission</button>
                        </form>
                    @endcan
                </x-card>
            @endif
        </div>

        {{-- Colonne latérale : budget dans le cadre repères, client, candidature --}}
        <aside class="min-w-0 space-y-6">
            <x-frame class="min-w-0 bg-surface p-6 sm:p-7">
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
                        <p class="mt-3 text-ink-soft">Candidature envoyée le {{ $myApplication->created_at->translatedFormat('j M') }} pour {{ App\Support\Money::fcfa($myApplication->proposed_fcfa) }} FCFA{{ $myApplication->delivery_days ? ', livrée en '.$myApplication->delivery_days.' jours' : '' }}. Le client choisira parmi les candidats ; vous verrez le statut ici.</p>
                        <x-chip :tone="$myApplication->status->tone()" class="mt-4">{{ $myApplication->status->label() }}</x-chip>
                    @elseif (! $mission->isOpen())
                        <h2 class="text-2xl">Candidatures closes</h2>
                        <p class="mt-3 text-ink-soft">Le client a déjà choisi son dev. D'autres missions vous attendent.</p>
                        <x-button :href="route('missions.index')" variant="secondary" class="mt-5 w-full">Voir les missions</x-button>
                    @elseif (! $user)
                        <h2 class="text-2xl">Décrochez cette mission</h2>
                        <p class="mt-3 text-ink-soft">Un message et votre prix : le client voit votre profil, vos compétences et vos cauris.</p>
                        <x-button :href="route('register')" size="lg" class="mt-5 w-full" data-cta="principal">{{ App\Support\Copy::SIGNUP_CTA }}</x-button>
                        <x-reassurance :items="App\Support\Copy::SIGNUP_REASSURANCE" class="mt-3 justify-center" />
                        <p class="mt-4 text-center text-sm"><a href="{{ route('login') }}" class="text-ink-muted underline-offset-4 hover:text-ink hover:underline">Déjà membre ? Se connecter</a></p>
                    @else
                        <h2 class="text-2xl">Décrochez cette mission</h2>
                        <p class="mt-3 text-ink-soft">Un message et votre prix : le client voit votre profil, vos compétences et vos cauris.</p>
                        <form method="post" action="{{ route('missions.apply', $mission) }}" class="mt-5 space-y-5" novalidate>
                            @csrf
                            <x-field label="Votre message" name="message" hint="Votre expérience utile, votre approche, vos disponibilités.">
                                <x-textarea name="message" rows="5" maxlength="2000" required />
                            </x-field>
                            <div class="grid grid-cols-2 gap-3">
                                <x-field label="Votre prix (FCFA)" name="proposed_fcfa">
                                    <x-input name="proposed_fcfa" inputmode="numeric" :value="App\Support\Money::fcfa($mission->budget_fcfa)" required />
                                </x-field>
                                <x-field label="Délai de livraison (jours)" name="delivery_days">
                                    <x-input name="delivery_days" type="number" min="1" max="180" inputmode="numeric" placeholder="10" required />
                                </x-field>
                            </div>
                            <x-button type="submit" class="w-full">Postuler à cette mission</x-button>
                            <p class="text-center text-sm text-ink-muted">Gratuit · Une seule candidature par mission, soignez-la.</p>
                        </form>
                    @endif
                </x-card>
            @endunless
        </aside>
    </div>
</x-layouts.app>
