@props(['tone' => 'info', 'dot' => false])

{{-- Puce douce. Tons (08-design.md, « Correspondances d'usage ») :
     info (Ouverte, Disponible) · warm (En cours, Nouveau) · done (Terminée : aplat avec coche)
     · muted (Annulée, En poste) · neutral (compétences). L'ambre n'est jamais du texte : warm = texte secondary-800. --}}
@php
    $classes = match ($tone) {
        'warm' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-950 dark:text-secondary-300',
        'done' => 'bg-primary-600 text-white dark:bg-secondary-400 dark:text-primary-950',
        'muted' => 'bg-bg-alt text-ink-muted ring-1 ring-line',
        'neutral' => 'bg-bg-alt text-ink-soft ring-1 ring-line',
        default => 'bg-primary-50 text-primary-700 dark:bg-primary-900 dark:text-primary-200',
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
