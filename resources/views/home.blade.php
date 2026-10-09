<x-layouts.app>
    <x-slot:head>
        <link rel="preload" as="image" type="image/webp" media="(min-width: 768px)" fetchpriority="high"
              imagesrcset="/images/hero/portrait-480.webp 480w, /images/hero/portrait-800.webp 800w"
              imagesizes="(min-width: 1280px) 520px, (min-width: 1024px) 40vw, 448px">
    </x-slot:head>

    {{-- Accueil (08-design.md, « Hero de l'accueil » : modèle stiesas.com, en aplats indigo et ambre). --}}

    <x-slot:hero>
        {{-- Bloc 1 : bandeau hero, aplat primary-800, glissé sous la navigation flottante --}}
        <section id="hero" class="relative -mt-[68px] overflow-hidden bg-primary-800 pt-[68px] text-white sm:-mt-[76px] sm:pt-[76px] dark:bg-primary-950">
            {{-- Décor : 3 arcs concentriques, trait de 1 px blanc à 6 %, coin inférieur gauche --}}
            <svg data-decor class="pointer-events-none absolute bottom-0 left-0 h-[560px] w-[560px] sm:h-[760px] sm:w-[760px]" viewBox="0 0 760 760" fill="none" aria-hidden="true">
                @foreach ([340, 500, 660] as $radius)
                    <circle cx="0" cy="760" r="{{ $radius }}" stroke="white" stroke-opacity=".06" stroke-width="1" />
                @endforeach
            </svg>

            <div class="relative mx-auto grid max-w-page items-center gap-10 px-4 pb-14 pt-10 sm:px-6 sm:pb-20 sm:pt-16 lg:grid-cols-[55fr_45fr] lg:gap-12">
                {{-- Colonne texte --}}
                <div>

                    <h1 class="text-balance text-[38px] leading-[1.1] text-white sm:text-[48px]">
                        Quittez les groupes WhatsApp,
                        <em class="block font-serif italic text-secondary-400">les missions viennent à vous.</em>
                    </h1>

                    <p class="mt-6 max-w-xl text-[17px] leading-relaxed text-white/80">
                        Les startups d'Afrique francophone publient leurs missions en FCFA :
                        vous postulez en 2 minutes, et vous êtes payé en Mobile Money.
                    </p>

                    {{-- Un seul CTA principal (ambre) ; le second profil a un simple lien texte. --}}
                    <div class="mt-8 flex flex-col items-start gap-4">
                        @auth
                            <x-button :href="route('missions.index', ['pour_moi' => 1])" variant="cta" size="lg" class="w-full sm:w-auto" data-cta="principal">{{ App\Support\Copy::MEMBER_CTA }}</x-button>
                            <a href="{{ route('missions.create') }}" class="text-[15px] text-white/70 underline-offset-4 hover:text-white hover:underline">Publier une mission →</a>
                        @else
                            <x-button :href="route('register')" variant="cta" size="lg" class="w-full sm:w-auto" data-cta="principal">{{ App\Support\Copy::SIGNUP_CTA }}</x-button>
                            <a href="{{ route('missions.create') }}" class="text-[15px] text-white/70 underline-offset-4 hover:text-white hover:underline">{{ App\Support\Copy::RECRUIT_LINK }}</a>
                        @endauth
                    </div>
                </div>

                {{-- Colonne visuel : image rassurante (générée par IA, validée par l'inspecteur), portrait 4:5 à partir
                     de 768 px, paysage 16:9 sur mobile, sous le CTA. Une seule image, chargée tout de suite et en priorité
                     haute (au-dessus de la ligne de flottaison sur ordinateur ; ~38 Ko sur mobile).
                     Sources et régénération : scripts/hero-images.php --}}
                <picture class="block min-w-0 md:mx-auto md:w-full md:max-w-md lg:max-w-none">
                    <source media="(min-width: 768px)" type="image/webp"
                            srcset="/images/hero/portrait-480.webp 480w, /images/hero/portrait-800.webp 800w"
                            sizes="(min-width: 1280px) 520px, (min-width: 1024px) 40vw, 448px">
                    <img src="/images/hero/paysage-960.webp"
                         srcset="/images/hero/paysage-640.webp 640w, /images/hero/paysage-960.webp 960w"
                         sizes="calc(100vw - 32px)"
                         width="960" height="540"
                         alt="Deux développeurs travaillent ensemble devant un ordinateur"
                         loading="eager" fetchpriority="high" decoding="async"
                         class="aspect-video w-full rounded-2xl border border-white/10 object-cover md:aspect-[4/5]">
                </picture>
            </div>
        </section>

        {{-- Bloc 2 : bande de garanties (la réassurance), aplat primary-900 --}}
        <section aria-label="Nos garanties" class="bg-primary-900 text-white">
            <dl class="mx-auto grid max-w-page gap-6 px-4 py-8 text-center sm:grid-cols-3 sm:px-6">
                @foreach ([
                    ['Payé en Mobile Money', 'Wave, Orange Money, MTN MoMo, Free, Moov'],
                    [App\Support\Copy::REASSURANCE_RISK, 'votre profil est en ligne en 2 minutes'],
                    ['Une réputation qui vous suit', 'chaque mission et chaque réponse vous rapportent des cauris'],
                ] as [$title, $line])
                    <div>
                        <dt class="font-serif text-lg font-medium text-secondary-400">{{ $title }}</dt>
                        <dd class="mt-1 text-sm text-white/75">{{ $line }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        {{-- Bloc 3 : bande de preuve sociale, chiffres réels mis en cache 10 min --}}
        <section aria-label="DevMarché en chiffres" class="border-b border-line bg-bg-alt">
            <dl class="mx-auto grid max-w-page grid-cols-2 gap-x-6 gap-y-8 px-4 py-10 text-center sm:px-6 lg:grid-cols-5">
                <div class="flex flex-col items-center">
                    <dt class="text-[11px] font-semibold uppercase tracking-[.14em] text-ink-muted">Devs inscrits</dt>
                    <dd class="mt-2 flex items-center gap-2">
                        <span class="flex -space-x-2" aria-hidden="true">
                            @foreach ($avatars as $avatar)
                                <x-avatar :user="$avatar" size="xs" />
                            @endforeach
                        </span>
                        <span class="font-serif text-xl font-medium text-ink" data-stat="devs">{{ $stats['devs'] }}</span>
                    </dd>
                </div>
                <div class="flex flex-col items-center">
                    <dt class="text-[11px] font-semibold uppercase tracking-[.14em] text-ink-muted">Pays</dt>
                    <dd class="mt-2 font-serif text-xl font-medium text-ink">
                        <span data-stat="countries">{{ $stats['countries'] }}</span>
                        <span class="ml-1 font-sans text-base tracking-wider" aria-hidden="true">{{ implode('', $stats['flags']) }}</span>
                    </dd>
                </div>
                <div class="flex flex-col items-center">
                    <dt class="text-[11px] font-semibold uppercase tracking-[.14em] text-ink-muted">Missions publiées</dt>
                    <dd class="mt-2 font-serif text-xl font-medium text-ink" data-stat="missions">{{ App\Support\Money::fcfa($stats['missions']) }}</dd>
                </div>
                <div class="flex flex-col items-center">
                    <dt class="text-[11px] font-semibold uppercase tracking-[.14em] text-ink-muted">Cauris gagnés</dt>
                    <dd class="mt-2 flex items-center gap-1.5 font-serif text-xl font-medium text-ink">
                        <x-cauri class="size-5 text-secondary-400" />
                        <span data-stat="cauris">{{ App\Support\Money::fcfa($stats['cauris']) }}</span>
                    </dd>
                </div>
                <div class="col-span-2 flex flex-col items-center lg:col-span-1">
                    <dt class="text-[11px] font-semibold uppercase tracking-[.14em] text-ink-muted">Pensé pour</dt>
                    <dd class="mt-2 font-serif text-xl font-medium text-ink">Wave · Orange Money · MTN MoMo</dd>
                </div>
            </dl>
        </section>
    </x-slot:hero>

    {{-- Dernières missions ouvertes (les questions récentes arrivent au lot 4) --}}
    @if ($openMissions->isNotEmpty())
        <section class="pt-16 sm:pt-24">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="max-w-2xl">
                    <p class="eyebrow mb-2">Missions ouvertes</p>
                    <h2 class="text-[32px] sm:text-[44px]">Des clients cherchent un dev en ce moment.</h2>
                    <p class="mt-3 text-ink-soft">Chaque mission affiche son budget en FCFA : postulez avec votre prix, le client choisit sur votre profil.</p>
                </div>
                <a href="{{ route('missions.index') }}" class="font-semibold text-primary-600 underline-offset-4 hover:underline dark:text-primary-300">Voir toutes les missions →</a>
            </div>
            <ul class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($openMissions as $mission)
                    <li class="min-w-0"><x-mission-card :mission="$mission" /></li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- CTA final : même aplat que le hero, même bouton ambre --}}
    @guest
        <section class="relative mt-20 overflow-hidden rounded-2xl bg-primary-800 px-6 py-14 text-center text-white sm:mt-28 sm:rounded-card sm:py-20 dark:bg-primary-950">
            <svg data-decor class="pointer-events-none absolute bottom-0 left-0 h-[420px] w-[420px]" viewBox="0 0 760 760" fill="none" aria-hidden="true">
                @foreach ([340, 500, 660] as $radius)
                    <circle cx="0" cy="760" r="{{ $radius }}" stroke="white" stroke-opacity=".06" stroke-width="1" />
                @endforeach
            </svg>
            <h2 class="relative mx-auto max-w-2xl text-[32px] text-white sm:text-[44px]">Votre prochaine mission n'est pas dans un groupe WhatsApp.</h2>
            <div class="relative mt-8">
                <x-button :href="route('register')" variant="cta" size="lg" class="w-full sm:w-auto">{{ App\Support\Copy::SIGNUP_CTA }}</x-button>
            </div>
        </section>
    @endguest
</x-layouts.app>
