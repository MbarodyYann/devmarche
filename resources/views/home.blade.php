<x-layouts.app>
    @php($reassurance = [App\Support\Copy::REASSURANCE_RISK, App\Support\Copy::REASSURANCE_NEXT])

    {{-- Hero (08-design.md, « Hero de l'accueil ») : la douleur, le mécanisme, UN CTA principal,
         la réassurance et le produit en action. Aucune image au-dessus de la ligne de flottaison. --}}
    <section class="dots -mx-4 -mt-10 px-4 pb-4 pt-12 text-center sm:-mx-6 sm:-mt-16 sm:px-6 sm:pt-20">
        <p class="eyebrow mb-4">Par et pour les devs d'Afrique francophone</p>

        <h1 class="mx-auto max-w-4xl text-[40px] sm:text-[64px]">
            Arrêtez de chercher vos missions
            <br class="hidden sm:block">
            dans les groupes <span class="whitespace-nowrap underline decoration-secondary-400 decoration-4 underline-offset-[10px]">WhatsApp</span>.
        </h1>

        <p class="mx-auto mt-6 max-w-2xl text-lg text-ink-soft">
            Les startups d'Afrique francophone publient leurs missions en FCFA sur DevMarché :
            vous postulez en 2 minutes, et vous êtes payé en Mobile Money.
        </p>

        {{-- Un seul CTA principal ; le second profil (qui recrute) a un simple lien texte. --}}
        <div class="mx-auto mt-8 flex max-w-sm flex-col items-center gap-4 sm:max-w-none">
            @auth
                <x-button :href="route('missions.index', ['pour_moi' => 1])" size="lg" class="w-full sm:w-auto" data-cta="principal">{{ App\Support\Copy::MEMBER_CTA }}</x-button>
                <a href="{{ route('missions.create') }}" class="text-[15px] text-ink-muted underline-offset-4 hover:text-ink hover:underline">Publier une mission →</a>
            @else
                <x-button :href="route('register')" size="lg" class="w-full sm:w-auto" data-cta="principal">{{ App\Support\Copy::SIGNUP_CTA }}</x-button>
                <a href="{{ route('missions.create') }}" class="text-[15px] text-ink-muted underline-offset-4 hover:text-ink hover:underline">Vous recrutez ? Publier une mission →</a>
            @endauth
        </div>

        @guest
            <ul class="mt-5 flex flex-wrap justify-center gap-x-5 gap-y-1 text-sm text-ink-muted">
                @foreach ($reassurance as $item)
                    <li class="flex items-center gap-1.5"><x-icon name="check" class="size-4 text-primary-600 dark:text-secondary-400" /> {{ $item }}</li>
                @endforeach
            </ul>
        @endguest

        {{-- Preuve sociale : de vrais devs, des chiffres réels (mis en cache 10 min) --}}
        <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
            <div class="flex -space-x-2.5" aria-hidden="true">
                @foreach ($avatars as $avatar)
                    <x-avatar :user="$avatar" size="sm" />
                @endforeach
            </div>
            <p class="text-ink-soft">
                Rejoignez <strong class="font-semibold text-ink">{{ $stats['devs'] }} devs</strong>
                de <strong class="font-semibold text-ink">{{ $stats['countries'] }} pays</strong>
                <span class="ml-1 tracking-widest" aria-hidden="true">{{ implode('', $stats['flags']) }}</span>
                @if ($stats['completedMissions'] > 0)
                    · <strong class="font-semibold text-ink">{{ $stats['completedMissions'] }} {{ $stats['completedMissions'] > 1 ? 'missions' : 'mission' }}</strong> {{ $stats['completedMissions'] > 1 ? 'terminées' : 'terminée' }}
                @endif
            </p>
        </div>

        {{-- Maquette de l'application, faite de vrais composants et de vraies données --}}
        @if ($mockMission && $mockDev)
            <x-frame class="mx-auto mt-14 max-w-4xl bg-bg-alt p-4 text-left sm:mt-16 sm:p-8 lg:p-10">
                <div class="relative lg:pb-16 lg:pr-56">
                    <x-mission-card :mission="$mockMission" class="lg:max-w-lg" />

                    <div class="hidden lg:block">
                        {{-- Candidature retenue, en léger chevauchement --}}
                        <x-card padding="p-5" class="absolute right-0 top-10 w-72 shadow-card-hover">
                            <p class="text-sm font-medium text-ink-muted">Candidature retenue</p>
                            <div class="mt-3 flex items-center gap-3">
                                <x-avatar :user="$mockDev" />
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-ink">{{ $mockDev->name }}</p>
                                    <x-reputation :points="$mockDev->reputation" />
                                </div>
                            </div>
                            <div class="mt-4 flex items-center justify-between border-t border-line pt-3">
                                <x-chip tone="warm" dot>En cours</x-chip>
                                <span class="font-serif text-lg font-medium text-ink">{{ App\Support\Money::fcfa((int) round($mockMission->budget_fcfa * 0.93 / 1000) * 1000) }} <span class="font-sans text-xs text-ink-muted">FCFA</span></span>
                            </div>
                        </x-card>

                        {{-- Notification de réputation --}}
                        <div class="absolute bottom-0 right-6 flex items-center gap-3 rounded-2xl border border-line bg-surface px-4 py-3 shadow-card-hover">
                            <x-cauri class="size-7 text-secondary-400" />
                            <p class="text-sm text-ink-soft"><strong class="font-semibold text-ink">+20 cauris</strong> · Mission terminée</p>
                        </div>
                    </div>
                </div>
            </x-frame>
        @endif

        {{-- Pensé pour le Mobile Money (noms en texte, aucun logo de marque) --}}
        <div class="mx-auto mt-12 max-w-3xl">
            <p class="text-sm text-ink-muted">Pensé pour le Mobile Money</p>
            <p class="mt-3 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 font-semibold text-ink-muted">
                @foreach (['Wave', 'Orange Money', 'Free Money', 'MTN MoMo', 'Moov Money'] as $provider)
                    <span>{{ $provider }}</span>
                    @if (! $loop->last)<span aria-hidden="true">·</span>@endif
                @endforeach
            </p>
        </div>
    </section>

    {{-- Dernières missions ouvertes (les questions récentes arrivent au lot 4) --}}
    @if ($openMissions->isNotEmpty())
        <section class="mt-20 sm:mt-28">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="eyebrow mb-2">Du travail entre devs</p>
                    <h2 class="text-[32px] sm:text-[44px]">Les dernières missions</h2>
                </div>
                <x-button :href="route('missions.index')" variant="secondary" size="sm">
                    Toutes les missions <x-icon name="arrow-right" class="size-4" />
                </x-button>
            </div>
            <ul class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($openMissions as $mission)
                    <li class="min-w-0"><x-mission-card :mission="$mission" /></li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Bloc de CTA final : la douleur en version courte, le même bouton, la même réassurance. --}}
    @guest
        <section class="dots mt-20 rounded-2xl border border-line bg-bg-alt px-6 py-14 text-center sm:mt-28 sm:rounded-card sm:py-20">
            <h2 class="mx-auto max-w-2xl text-[32px] sm:text-[44px]">Votre prochaine mission n'est pas dans un groupe WhatsApp.</h2>
            <div class="mt-8">
                <x-button :href="route('register')" size="lg" class="w-full sm:w-auto">{{ App\Support\Copy::SIGNUP_CTA }}</x-button>
            </div>
            <ul class="mt-5 flex flex-wrap justify-center gap-x-5 gap-y-1 text-sm text-ink-muted">
                @foreach ($reassurance as $item)
                    <li class="flex items-center gap-1.5"><x-icon name="check" class="size-4 text-primary-600 dark:text-secondary-400" /> {{ $item }}</li>
                @endforeach
            </ul>
        </section>
    @endguest
</x-layouts.app>
