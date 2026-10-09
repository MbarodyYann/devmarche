@props(['dev'])

<x-card :href="route('devs.show', $dev)" {{ $attributes->merge(['class' => 'flex h-full flex-col gap-4']) }}>
    <div class="flex items-start gap-3">
        <x-avatar :user="$dev" />
        <div class="min-w-0 flex-1">
            <p class="truncate font-display text-lg font-semibold text-ink">{{ $dev->name }}</p>
            @if ($dev->profile?->headline)
                <p class="truncate text-ink-muted">{{ $dev->profile->headline }}</p>
            @endif
        </div>
        <x-reputation :points="$dev->reputation" class="shrink-0" />
    </div>

    <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-ink-muted">
        @if ($dev->profile)
            <span class="inline-flex items-center gap-1">
                <span aria-hidden="true">{{ $dev->profile->flag() }}</span>
                {{ $dev->profile->city }}, {{ $dev->profile->countryName() }}
            </span>
            <x-status-pill :tone="$dev->profile->availability->tone()">{{ $dev->profile->availability->label() }}</x-status-pill>
        @endif
        @if ($dev->isNewMember())
            <x-new-badge />
        @endif
    </div>

    @if ($dev->skills->isNotEmpty())
        <div class="mt-auto flex flex-wrap gap-1.5">
            @foreach ($dev->skills->take(4) as $skill)
                <x-tag>{{ $skill->name }}</x-tag>
            @endforeach
            @if ($dev->skills->count() > 4)
                <span class="px-1 text-sm text-ink-muted">+{{ $dev->skills->count() - 4 }}</span>
            @endif
        </div>
    @endif
</x-card>
