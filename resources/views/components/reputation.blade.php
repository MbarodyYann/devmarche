@props(['points' => 0, 'size' => 'sm'])

{{-- Réputation en cauris : icône ambre suivie du chiffre (grand compteur Newsreader sur le profil).
     Rien n'est affiché à 0 : les cauris ne se montrent que pour un membre qui en a. --}}
@php($formatted = number_format($points, 0, ',', "\u{202F}"))

@if ($points > 0 && $size === 'lg')
    <div {{ $attributes->merge(['class' => 'flex flex-col']) }} title="Réputation : {{ $formatted }} cauris">
        <span class="flex items-center gap-3">
            <x-cauri class="size-10 text-secondary-400" />
            <span class="font-serif text-[56px] font-medium leading-none tracking-tight text-ink">{{ $formatted }}</span>
        </span>
        <span class="mt-2 text-sm font-medium text-ink-muted">
            cauris de réputation
        </span>
    </div>
@elseif ($points > 0)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 text-sm font-semibold tabular-nums text-ink']) }}
          title="Réputation : {{ $formatted }} cauris">
        <x-cauri />
        {{ $formatted }}
        <span class="sr-only">cauris de réputation</span>
    </span>
@endif
