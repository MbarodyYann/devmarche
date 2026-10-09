@props(['items', 'onDark' => false])

{{-- Ligne de réassurance : chaque élément est précédé d'une coche (la coche marque une garantie, pas un statut). --}}
<ul {{ $attributes->class(['flex flex-wrap gap-x-5 gap-y-1 text-sm', 'text-white/70' => $onDark, 'text-ink-muted' => ! $onDark]) }}>
    @foreach ((array) $items as $item)
        <li class="flex items-center gap-1.5">
            <x-icon name="check" @class(['size-4', 'text-secondary-400' => $onDark, 'text-primary-600 dark:text-secondary-400' => ! $onDark]) />
            {{ $item }}
        </li>
    @endforeach
</ul>
