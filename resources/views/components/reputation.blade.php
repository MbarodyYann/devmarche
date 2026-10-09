@props(['points' => 0, 'size' => 'sm'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 font-display font-semibold tabular-nums '.($size === 'lg' ? 'text-xl' : 'text-sm')]) }}
      title="Réputation : {{ number_format($points, 0, ',', ' ') }} cauris">
    <x-cauri @class(['size-6' => $size === 'lg', 'size-4' => $size !== 'lg']) />
    <span>{{ number_format($points, 0, ',', "\u{202F}") }}</span>
    <span class="sr-only">cauris de réputation</span>
</span>
