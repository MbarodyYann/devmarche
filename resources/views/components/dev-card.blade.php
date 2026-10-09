@props(['dev'])

{{-- Carte d'un dev (annuaire sur mobile et tablette). --}}
@php($profile = $dev->profile)

<x-card :href="route('devs.show', $dev)" padding="p-5" {{ $attributes->merge(['class' => 'flex h-full flex-col gap-4']) }}>
    <div class="flex items-start gap-3">
        <x-avatar :user="$dev" />
        <div class="min-w-0 flex-1">
            <p class="truncate font-semibold text-ink">{{ $dev->name }}</p>
            @if ($profile?->headline)
                <p class="truncate text-sm text-ink-muted">{{ $profile->headline }}</p>
            @endif
        </div>
        <x-reputation :points="$dev->reputation" class="shrink-0" />
    </div>
    @if ($profile)
        <p class="flex items-center gap-1.5 text-sm text-ink-muted">
            <x-icon name="map-pin" class="size-4" /> {{ $profile->city }}, {{ $profile->countryName() }}
        </p>
    @endif
    <x-skills :skills="$dev->skills" :limit="4" :link="false" />
    <div class="mt-auto flex flex-wrap items-center gap-2">
        @if ($profile)
            <x-chip :tone="$profile->availability->tone()" dot>{{ $profile->availability->label() }}</x-chip>
        @endif
        @if ($dev->isNewMember())
            <x-new-badge />
        @endif
    </div>
</x-card>
