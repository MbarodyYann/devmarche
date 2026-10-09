@props(['tone' => 'secondary'])

{{-- Pastille de statut. Tons : secondary (Ouverte), primary (En cours), success (Terminée), muted (Annulée). --}}
@php
    [$bg, $dot] = match ($tone) {
        'primary' => ['bg-primary-soft text-primary-strong', 'bg-primary'],
        'success' => ['bg-success/12 text-success', 'bg-success'],
        'danger' => ['bg-danger/12 text-danger', 'bg-danger'],
        'muted' => ['bg-border/60 text-ink-muted', 'bg-ink-muted'],
        default => ['bg-secondary-soft text-secondary', 'bg-secondary'],
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-sm font-semibold $bg"]) }}>
    <span class="size-1.5 rounded-full {{ $dot }}" aria-hidden="true"></span>
    {{ $slot }}
</span>
