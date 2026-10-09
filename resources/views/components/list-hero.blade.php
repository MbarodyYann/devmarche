@props(['start', 'end', 'subtitle', 'action' => null])

{{-- En-tête compact des pages de liste (08, dernière section) : bandeau primary-800 avec arcs,
     H1 dont la fin est en italique ambre, sous-titre (masqué sur mobile), action du membre à droite.
     Aucun CTA visiteur ici : celui de la navigation suffit et reste visible.
     Le bas est laissé libre pour la carte de filtres « à cheval » (pb). --}}
<section id="hero" @class([
    'relative -mt-[68px] overflow-hidden bg-primary-800 pt-[68px] text-white sm:-mt-[76px] sm:pt-[76px] dark:bg-primary-900',
])>
    <svg data-decor class="pointer-events-none absolute bottom-0 left-0 h-[420px] w-[420px]" viewBox="0 0 760 760" fill="none" aria-hidden="true">
        @foreach ([340, 500, 660] as $radius)
            <circle cx="0" cy="760" r="{{ $radius }}" stroke="white" stroke-opacity=".06" stroke-width="1" />
        @endforeach
    </svg>

    {{-- Respiration (08) : 32 px sous la navigation sur mobile, 60 px sur ordinateur ; en bas, l'espace
         de la carte de filtres qui chevauche le bandeau (24 / 40 px) plus 40 / 72 px d'air. --}}
    <div class="relative mx-auto flex max-w-page flex-col gap-4 px-4 pb-16 pt-8 sm:px-6 sm:pb-24 sm:pt-12 lg:flex-row lg:items-center lg:justify-between lg:gap-10 lg:pb-28 lg:pt-[60px]">
        <div class="min-w-0">
            <h1 class="text-balance text-[30px] leading-[1.1] text-white sm:text-[40px]">
                {{ $start }} <em class="font-serif italic text-secondary-400">{{ $end }}</em>
            </h1>
            <p class="mt-4 hidden max-w-2xl leading-[1.6] text-white/80 sm:block">{{ $subtitle }}</p>
        </div>

        {{-- Visiteur : pas de CTA ici, celui de la navigation reste visible (décision du représentant).
             Membre : l'action de la page (publier, poser une question). --}}
        @auth
            @if ($action)
                <div class="shrink-0">
                    <x-button :href="route($action['route'])" variant="cta" size="sm" class="sm:min-h-11 sm:px-4 sm:text-[15px]" data-cta="principal"><x-icon name="plus" class="size-4" /> {{ $action['label'] }}</x-button>
                </div>
            @endif
        @endauth
    </div>
</section>
