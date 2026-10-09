@props(['rows', 'label', 'value', 'format' => null])

{{-- Barres horizontales en CSS (aucune bibliothèque) : longueur proportionnelle à la plus grande valeur. --}}
@php($max = max(1, collect($rows)->max($value)))
<ul {{ $attributes->class('space-y-3') }}>
    @foreach ($rows as $row)
        <li class="grid grid-cols-[minmax(0,9rem)_minmax(0,1fr)_auto] items-center gap-3 text-sm sm:grid-cols-[minmax(0,12rem)_minmax(0,1fr)_auto]">
            <span class="truncate text-ink">{{ $row[$label] }}</span>
            <span class="h-3 overflow-hidden rounded-full bg-bg-alt ring-1 ring-line" aria-hidden="true">
                <span class="block h-full rounded-full bg-secondary-400" style="width: {{ max(2, round($row[$value] / $max * 100)) }}%"></span>
            </span>
            <span class="whitespace-nowrap text-right font-semibold text-ink">{{ $format ? $format($row) : $row[$value] }}</span>
        </li>
    @endforeach
</ul>
