@props(['dev'])

{{-- Ligne du registre des devs. Mobile : empilé. Ordinateur : colonnes alignées. --}}
@php($profile = $dev->profile)

<a href="{{ route('devs.show', $dev) }}"
   class="group grid grid-cols-[auto_1fr] items-start gap-x-4 gap-y-2 px-1 py-4 transition-colors hover:bg-accent/12 focus-visible:bg-accent/12 sm:px-3 lg:grid-cols-[auto_minmax(0,1.3fr)_9rem_minmax(0,1.2fr)_6.5rem_5rem_1.25rem] lg:items-center">
    <x-avatar :user="$dev" class="row-span-3 lg:row-span-1" />

    <div class="min-w-0">
        <p class="flex flex-wrap items-center gap-x-3 gap-y-1">
            <span class="truncate text-lg font-bold text-ink">{{ $dev->name }}</span>
            @if ($dev->isNewMember())
                <x-new-badge />
            @endif
        </p>
        @if ($profile?->headline)
            <p class="truncate text-ink-muted">{{ $profile->headline }}</p>
        @endif
    </div>

    <p class="etiquette col-start-2 text-ink-muted lg:col-start-auto">
        @if ($profile)
            {{ $profile->city }} · {{ $profile->country }}
        @endif
    </p>

    <div class="col-start-2 min-w-0 lg:col-start-auto">
        {{-- Les étiquettes ne sont pas des liens ici : la ligne entière en est déjà un. --}}
        <x-skills :skills="$dev->skills" :limit="4" :link="false" class="text-xs!" />
    </div>

    <div class="col-start-2 flex items-center justify-between gap-4 lg:contents">
        @if ($profile)
            <span><x-stamp :tone="$profile->availability->tone()">{{ $profile->availability->stamp() }}</x-stamp></span>
        @endif
        <x-reputation :points="$dev->reputation" class="lg:justify-self-end" />
    </div>

    <span class="hidden text-xl font-bold text-primary-strong transition-transform group-hover:translate-x-1 lg:block" aria-hidden="true">→</span>
</a>
