@props(['mission'])

{{-- Carte mission (08 v3) : statut et date, titre serif, extrait de 2 lignes, compétences,
     budget en gros chiffre, client et nombre de candidatures. --}}
<x-card :href="route('missions.show', $mission)" padding="p-5 sm:p-6" {{ $attributes->merge(['class' => 'flex h-full min-w-0 flex-col gap-4']) }}>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <x-chip :tone="$mission->status->tone()">{{ $mission->status->label() }}</x-chip>
        <span class="text-sm text-ink-muted">{{ $mission->created_at->translatedFormat('j M Y') }}</span>
    </div>

    <div class="min-w-0">
        <h3 class="text-[22px] leading-snug">{{ $mission->title }}</h3>
        <p class="mt-2 line-clamp-2 text-ink-soft">{{ $mission->description }}</p>
    </div>

    <p class="flex items-center gap-1.5 text-sm text-ink-muted">
        @if ($mission->isRemote())
            <x-icon name="link" class="size-4" /> Télétravail
        @else
            <x-icon name="map-pin" class="size-4" /> {{ $mission->locationLabel() }} <span aria-hidden="true">{{ $mission->flag() }}</span>
        @endif
    </p>

    <x-skills :skills="$mission->skills" :limit="4" :link="false" />

    <div class="mt-auto flex items-end justify-between gap-3 border-t border-line pt-4">
        <x-budget :amount="$mission->budget_fcfa" />
        <div class="flex items-center gap-2 text-sm text-ink-muted">
            <x-avatar :user="$mission->client" size="xs" />
            <span>{{ $mission->applications_count }} {{ $mission->applications_count > 1 ? 'candidatures' : 'candidature' }}</span>
        </div>
    </div>
</x-card>
