@props(['points' => 0, 'size' => 'sm'])

{{-- Réputation en cauris. Grand format : compteur en Danfo et étiquette CAURIS. --}}
@php($formatted = number_format($points, 0, ',', "\u{202F}"))

@if ($size === 'lg' && $points === 0)
    {{-- Le « 0 » de Danfo, seul, se lit comme un pavé : on écrit la phrase à la place. --}}
    <div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
        <x-cauri class="size-9 text-accent" />
        <span class="etiquette text-ink-muted">Pas encore de cauris</span>
    </div>
@elseif ($size === 'lg')
    <div {{ $attributes->merge(['class' => 'inline-flex flex-col items-start']) }} title="Réputation : {{ $formatted }} cauris">
        <span class="flex items-center gap-2">
            <x-cauri class="size-9 text-accent sm:size-11" />
            <span class="font-display text-4xl leading-none text-ink sm:text-[4rem]">{{ $formatted }}</span>
        </span>
        <span class="etiquette mt-1 text-ink-muted">Cauris</span>
    </div>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 font-mono text-sm font-bold tabular-nums text-ink']) }}
          title="Réputation : {{ $formatted }} cauris">
        <x-cauri class="size-4 text-accent" />
        {{ $formatted }}
        <span class="sr-only">cauris de réputation</span>
    </span>
@endif
