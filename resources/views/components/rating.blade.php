@props(['average' => null, 'count' => 0])

{{-- Note moyenne compacte : « ★ 4,8 (6) ». Rien tant qu'il n'y a pas d'avis. --}}
@if ($count > 0)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 text-sm font-semibold tabular-nums text-ink']) }}
          title="Note moyenne : {{ number_format((float) $average, 1, ',', '') }} sur 5, {{ $count }} {{ $count > 1 ? 'avis' : 'avis' }}">
        <x-icon name="star" class="size-4 fill-current text-secondary-400" />
        {{ number_format((float) $average, 1, ',', '') }}
        <span class="font-normal text-ink-muted">({{ $count }})</span>
        <span class="sr-only">sur 5, {{ $count }} avis</span>
    </span>
@endif
