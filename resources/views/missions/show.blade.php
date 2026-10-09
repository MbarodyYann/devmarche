<x-layouts.app :title="$mission->title" :description="$mission->description">
    @php
        $user = auth()->user();
        $isClient = $user && $user->id === $mission->client_id;
    @endphp

    <a href="{{ route('missions.index') }}" class="mb-4 inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-ink">
        <x-icon name="arrow-left" class="size-4" /> Toutes les missions
    </a>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        {{-- Contenu --}}
        <div class="min-w-0 space-y-6">
            <x-card>
                <div class="flex flex-wrap items-center gap-2">
                    <x-chip :tone="$mission->status->tone()">{{ $mission->status->label() }}</x-chip>
                    @if ($mission->payment)
                        <x-chip :tone="$mission->payment->status->tone()" data-payment-status>{{ $mission->payment->status->label() }}</x-chip>
                    @endif
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
                <x-share class="mt-6 border-t border-line pt-5" :title="$mission->title.' · '.App\Support\Money::fcfa($mission->budget_fcfa).' FCFA'" :url="route('missions.show', $mission)" />
                <x-report-button type="mission" :content="$mission" :owner="$mission->client_id" class="mt-2" />
            </x-card>

            {{-- Avis : le client note le freelance une fois la mission terminée ; l'avis est ensuite public. --}}
            @can('review', $mission)
                <x-card id="avis" class="scroll-mt-24">
                    <h2 class="text-[28px]">Comment s'est passée la mission avec {{ $mission->freelancer->name }} ?</h2>
                    <p class="mt-2 text-ink-soft">Votre note et votre commentaire apparaissent sur son profil, avec le budget et la durée de la mission.</p>
                    <form method="post" action="{{ route('missions.review', $mission) }}" class="mt-6 space-y-5">
                        @csrf
                        <x-star-input name="rating" label="Note globale" />
                        <div class="grid gap-5 sm:grid-cols-3">
                            @foreach (App\Models\Review::CRITERIA as $field => $label)
                                <x-star-input :name="$field" :label="$label" />
                            @endforeach
                        </div>
                        <x-field label="Votre commentaire" name="comment" :optional="true" hint="Ce qui s'est bien passé, ce qui pourrait l'être mieux.">
                            <x-textarea name="comment" rows="4" maxlength="1000" />
                        </x-field>
                        <x-button type="submit" class="w-full sm:w-auto">Publier mon avis</x-button>
                    </form>
                </x-card>
            @endcan

            @if ($mission->review)
                <x-card id="avis" class="scroll-mt-24" data-mission-review>
                    <h2 class="text-[28px]">Avis du client</h2>
                    <x-review-detail :review="$mission->review" class="mt-4" />
                </x-card>
            @endif

            {{-- Candidatures : vue du client --}}
            @if ($isClient)
                <x-card id="candidatures" class="scroll-mt-24">
                    <h2 class="text-[28px]">{{ $mission->isOpen() ? 'Choisissez votre dev' : 'Candidatures' }} <span class="text-ink-muted">({{ $applications->count() }})</span></h2>
                    {{-- Aide au choix : seulement tant que la mission est ouverte (le choix n'est pas fait). --}}
                    @if ($mission->isOpen())
                        @if ($applications->whereNotNull('delivery_days')->isNotEmpty())
                            <p class="mt-1 text-sm text-ink-muted">Délai moyen proposé : <strong class="font-semibold text-ink">{{ (int) round($applications->avg('delivery_days')) }} jours</strong></p>
                        @endif
                        <p class="mt-2 text-ink-soft">Comparez les prix, les messages et les cauris. Quand vous retenez un dev, les autres candidats sont prévenus.</p>
                    @endif

                    @can('complete', $mission)
                        <div class="mt-5 flex flex-col gap-3 rounded-2xl bg-bg-alt p-4 ring-1 ring-line sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-ink">{{ $mission->freelancer->name }} travaille sur votre mission.</p>
                                <p class="text-sm text-ink-muted">Le dev reçoit +20 cauris de réputation{{ $mission->payment?->status === App\Enums\PaymentStatus::Held ? ', et les fonds sécurisés lui sont versés' : '' }}.</p>
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
                                        <x-rating :average="$application->user->reviews_avg" :count="$application->user->reviews_count" />
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
                                            {{-- Retenir = sécuriser les fonds (paiement Mobile Money simulé). --}}
                                            <x-button :href="route('payments.create', [$mission, $application])" size="sm">Retenir ce dev</x-button>
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

            {{-- Paiement simulé (séquestre) : détail pour le client et le dev retenu seulement. --}}
            @if ($mission->payment && $user && in_array($user->id, [$mission->client_id, $mission->freelancer_id], true))
                <x-card padding="p-6" data-payment>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="font-sans text-sm font-semibold tracking-normal text-ink-muted">Paiement Mobile Money</h2>
                        <x-chip :tone="$mission->payment->status->tone()">{{ $mission->payment->status->label() }}</x-chip>
                    </div>
                    <p class="mt-3 font-serif text-[28px] font-medium leading-none text-ink">{{ App\Support\Money::fcfa($mission->payment->amount_fcfa) }} <span class="font-sans text-sm text-ink-muted">FCFA</span></p>
                    <dl class="mt-4 space-y-1.5 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-ink-muted">Opérateur</dt><dd class="font-medium text-ink">{{ $mission->payment->providerLabel() }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-ink-muted">Numéro</dt><dd class="font-medium text-ink">{{ $mission->payment->maskedPhone() }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-ink-muted">Référence</dt><dd class="font-medium text-ink">{{ $mission->payment->reference }}</dd></div>
                    </dl>
                    <p class="mt-4 text-sm text-ink-muted">
                        @switch($mission->payment->status)
                            @case(App\Enums\PaymentStatus::Held)
                                Libéré au dev quand le client confirme la fin de la mission.
                                @break
                            @case(App\Enums\PaymentStatus::Released)
                                Versé au dev à la fin de la mission.
                                @break
                            @default
                                Rendu au client après l'annulation.
                        @endswitch
                        Simulation : aucun argent réel n'est débité.
                    </p>
                </x-card>
            @endif

            {{-- Qui publie (masqué pour l'auteur : c'est lui) et à qui la mission est confiée. Pour un client,
                 on montre son activité de client, pas des cauris (ils récompensent le travail des devs). --}}
            @if (! $isClient || $mission->freelancer)
                <x-card padding="p-6">
                    @unless ($isClient)
                        <h2 class="font-sans text-sm font-semibold tracking-normal text-ink-muted">Publiée par</h2>
                        <a href="{{ route('devs.show', $mission->client) }}" class="mt-3 flex items-center gap-3">
                            <x-avatar :user="$mission->client" />
                            <span class="min-w-0">
                                <span class="block truncate font-semibold text-ink">{{ $mission->client->name }}</span>
                                <span class="block truncate text-sm text-ink-muted">{{ $mission->client->profile?->headline }}</span>
                            </span>
                        </a>
                        <p class="mt-3 text-sm text-ink-muted" data-client-activity>
                            <span class="whitespace-nowrap">{{ $mission->client->missions_count }} {{ $mission->client->missions_count > 1 ? 'missions publiées' : 'mission publiée' }} ·</span>
                            <span class="whitespace-nowrap">Membre depuis {{ $mission->client->created_at->translatedFormat('M Y') }}</span>
                        </p>
                    @endunless

                    @if ($mission->freelancer)
                        <h2 @class(['font-sans text-sm font-semibold tracking-normal text-ink-muted', 'mt-6 border-t border-line pt-5' => ! $isClient])>Confiée à</h2>
                        <a href="{{ route('devs.show', $mission->freelancer) }}" class="mt-3 flex items-center gap-3">
                            <x-avatar :user="$mission->freelancer" />
                            <span class="block truncate font-semibold text-ink">{{ $mission->freelancer->name }}</span>
                        </a>
                    @endif
                </x-card>
            @endif

            @unless ($isClient)
                <x-card padding="p-6" id="postuler" class="scroll-mt-24">
                    @if ($myApplication)
                        <h2 class="text-2xl">Votre candidature</h2>
                        <p class="mt-3 text-ink-soft">Candidature envoyée le {{ $myApplication->created_at->translatedFormat('j M') }} pour {{ App\Support\Money::fcfa($myApplication->proposed_fcfa) }} FCFA{{ $myApplication->delivery_days ? ', livrée en '.$myApplication->delivery_days.' jours' : '' }}. Le client choisira parmi les candidats ; vous serez prévenu de sa décision.</p>
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

    {{-- Barre d'action collante sous 1 024 px (au-dessus de la barre du bas) : le budget et l'action,
         pour ne pas avoir à descendre sous une longue description. Elle s'efface quand la carte
         d'action est à l'écran. --}}
    @php
        $barTarget = $isClient ? 'candidatures' : 'postuler';
        $showBar = $mission->isOpen() && ($isClient ? $applications->isNotEmpty() : ! $myApplication);
    @endphp
    @if ($showBar)
        <div class="h-20 lg:hidden" aria-hidden="true"></div>
        <div data-action-bar
             x-data="{ hidden: false }"
             x-init="const target = document.getElementById('{{ $barTarget }}'); if (target) new IntersectionObserver(([entry]) => hidden = entry.isIntersecting).observe(target)"
             x-bind:class="hidden && 'pointer-events-none translate-y-3 opacity-0'"
             x-bind:aria-hidden="hidden.toString()"
             class="fixed inset-x-2 z-30 transition duration-200 motion-reduce:transition-none lg:hidden"
             style="bottom: calc(5.75rem + env(safe-area-inset-bottom))">
            <div class="mx-auto flex max-w-page items-center justify-between gap-3 rounded-2xl border border-line bg-surface px-3 py-2 shadow-card-hover">
                <p class="min-w-0 whitespace-nowrap">
                    <span class="font-serif text-xl font-medium tabular-nums text-ink">{{ App\Support\Money::fcfa($mission->budget_fcfa) }}</span>
                    <span class="text-sm text-ink-muted max-[359px]:sr-only">FCFA</span>
                </p>
                @if ($isClient)
                    <x-button href="#candidatures" size="xs">Voir les candidatures ({{ $applications->count() }})</x-button>
                @elseif (! $user)
                    <x-button :href="route('register')" size="xs">{{ App\Support\Copy::SIGNUP_CTA }}</x-button>
                @else
                    <x-button href="#postuler" size="xs">Postuler à cette mission</x-button>
                @endif
            </div>
        </div>
    @endif
</x-layouts.app>
