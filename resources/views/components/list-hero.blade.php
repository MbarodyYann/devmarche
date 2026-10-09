@props(['start', 'end', 'subtitle', 'action' => null])

{{-- En-tête compact des pages de liste (08, dernière section) : bandeau primary-800 avec arcs,
     H1 dont la fin est en italique ambre, sous-titre (masqué sur mobile), CTA visiteur ou action membre.
     Le bas est laissé libre pour la carte de filtres « à cheval » (pb). --}}
<section id="hero" @class([
    'relative -mt-[68px] overflow-hidden bg-primary-800 pt-[68px] text-white sm:-mt-[76px] sm:pt-[76px] dark:bg-primary-950',
])>
    <svg data-decor class="pointer-events-none absolute bottom-0 left-0 h-[420px] w-[420px]" viewBox="0 0 760 760" fill="none" aria-hidden="true">
        @foreach ([340, 500, 660] as $radius)
            <circle cx="0" cy="760" r="{{ $radius }}" stroke="white" stroke-opacity=".06" stroke-width="1" />
        @endforeach
    </svg>

    <div @class([
        'relative mx-auto flex max-w-page flex-col gap-4 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:gap-10',
        'pb-9 pt-2 sm:pb-16 sm:pt-8' => auth()->guest(),
        'pb-9 pt-1 sm:pb-14 sm:pt-5' => auth()->check(),
    ])>
        <div class="min-w-0">
            <h1 class="text-balance text-[30px] leading-[1.1] text-white sm:text-[40px]">
                {{ $start }} <em class="font-serif italic text-secondary-400">{{ $end }}</em>
            </h1>
            <p class="mt-2 hidden max-w-2xl text-white/80 sm:block">{{ $subtitle }}</p>
        </div>

        @guest
            {{-- Sur mobile, le bouton et le lien tiennent sur une ligne : l'en-tête reste compact. --}}
            <div class="flex shrink-0 flex-wrap items-center gap-x-4 gap-y-2 lg:flex-col lg:items-end">
                <x-button :href="route('register')" variant="cta" size="sm" class="sm:min-h-11 sm:px-4 sm:text-[15px]" data-cta="principal">{{ App\Support\Copy::SIGNUP_CTA }}</x-button>
                <a href="{{ route('missions.create') }}" class="text-sm text-white/70 underline-offset-4 hover:text-white hover:underline">{{ App\Support\Copy::RECRUIT_LINK }}</a>
            </div>
        @else
            @if ($action)
                <div class="shrink-0">
                    <x-button :href="route($action['route'])" variant="cta" size="sm" class="sm:min-h-11 sm:px-4 sm:text-[15px]" data-cta="principal"><x-icon name="plus" class="size-4" /> {{ $action['label'] }}</x-button>
                </div>
            @endif
        @endguest
    </div>
</section>
