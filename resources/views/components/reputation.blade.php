@props(['points' => 0, 'size' => 'sm'])

{{-- Réputation en cauris : icône ambre suivie du chiffre (grand compteur Newsreader sur le profil). --}}
@php($formatted = number_format($points, 0, ',', "\u{202F}"))

@if ($size === 'lg')
    <div {{ $attributes->merge(['class' => 'flex flex-col']) }} title="Réputation : {{ $formatted }} cauris">
        <span class="flex items-center gap-3">
            <x-cauri class="size-10 text-secondary-400" />
            <span class="font-serif text-[56px] font-medium leading-none tracking-tight text-ink">{{ $formatted }}</span>
        </span>
        <span class="mt-2 text-sm font-medium text-ink-muted">
            {{ $points === 0 ? 'Pas encore de cauris : chaque réponse utile compte.' : 'cauris de réputation' }}
        </span>
    </div>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 text-sm font-semibold tabular-nums text-ink']) }}
          title="Réputation : {{ $formatted }} cauris">
        <x-cauri />
        {{ $formatted }}
        <span class="sr-only">cauris de réputation</span>
    </span>
@endif
