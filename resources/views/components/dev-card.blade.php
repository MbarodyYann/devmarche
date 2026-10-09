@props(['dev'])

{{-- Carte d'un dev (annuaire, sur tous les écrans) : ce sont des personnes, pas des lignes.
     Haut : avatar 56 px, nom, titre tronqué, Nouveau ; cauris à droite. Milieu : lieu et disponibilité.
     Bas, sous un filet : 4 compétences au plus, calées en bas pour aligner les cartes d'une rangée. --}}
@php($profile = $dev->profile)

<x-card :href="route('devs.show', $dev)" padding="p-5 sm:p-6" {{ $attributes->merge(['class' => 'flex h-full min-w-0 flex-col gap-4']) }}>
    <div class="flex min-w-0 items-start gap-3">
        <x-avatar :user="$dev" class="size-14!" />
        <div class="min-w-0 flex-1">
            <p class="truncate font-serif text-xl font-medium leading-tight text-ink">{{ $dev->name }}</p>
            @if ($profile?->headline)
                <p class="mt-0.5 truncate text-sm text-ink-muted">{{ $profile->headline }}</p>
            @endif
            @if ($dev->isNewMember())
                <x-new-badge class="mt-2" />
            @endif
        </div>
        <x-reputation :points="$dev->reputation" class="shrink-0" />
    </div>

    @if ($profile)
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-ink-muted">
            <span class="flex min-w-0 items-center gap-1.5">
                <x-icon name="map-pin" class="size-4 shrink-0" />
                <span class="truncate">{{ $profile->city }}, {{ $profile->countryName() }}</span>
                <span aria-hidden="true">{{ $profile->flag() }}</span>
            </span>
            <x-chip :tone="$profile->availability->tone()">{{ $profile->availability->label() }}</x-chip>
        </div>
    @endif

    <div class="mt-auto border-t border-line pt-4">
        @if ($dev->skills->isNotEmpty())
            <x-skills :skills="$dev->skills" :limit="4" :link="false" />
        @else
            <p class="text-sm text-ink-muted">Compétences à venir</p>
        @endif
    </div>
</x-card>
