@props(['tone' => 'secondary', 'dot' => false])

{{-- Puce douce. Tons : secondary (Ouverte, Disponible), primary (En cours, Nouveau),
     done (Terminée : plein avec coche), muted (Annulée, En poste), neutral (compétences). --}}
@php
    $classes = match ($tone) {
        'primary' => 'bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300',
        'done' => 'bg-secondary-700 text-white dark:bg-secondary-400 dark:text-night',
        'muted' => 'bg-bg-alt text-ink-muted ring-1 ring-line',
        'neutral' => 'bg-bg-alt text-ink-soft ring-1 ring-line',
        default => 'bg-secondary-50 text-secondary-700 dark:bg-secondary-950 dark:text-secondary-300',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-medium $classes"]) }}>
    @if ($tone === 'done')
        <x-icon name="check" class="size-3.5" />
    @elseif ($dot)
        <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
