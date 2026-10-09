{{-- Bandeau de simulation du paiement : clair et sans ambiguïté (aucun argent réel). --}}
<div role="note" data-simulation {{ $attributes->class('flex items-start gap-3 rounded-2xl border border-secondary-300 bg-secondary-50 px-4 py-3 text-primary-900 dark:border-secondary-800 dark:bg-secondary-950 dark:text-white') }}>
    <x-icon name="alert" class="mt-0.5 size-5 shrink-0" />
    <p><strong class="font-semibold">Simulation : aucun argent réel n'est débité.</strong> Aucun numéro n'est contacté et rien n'est envoyé à un opérateur.</p>
</div>
