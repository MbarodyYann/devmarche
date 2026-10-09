@props(['skills', 'limit' => null, 'link' => true])

{{-- Compétences en étiquettes mono séparées par « / » (jamais de pilules). --}}
@php
    $visible = $limit ? $skills->take($limit) : $skills;
    $rest = $skills->count() - $visible->count();
@endphp

@if ($skills->isNotEmpty())
    <p {{ $attributes->merge(['class' => 'etiquette text-ink']) }}>
        @foreach ($visible as $skill)
            @if ($link)
                <a href="{{ route('devs.index', ['competence' => $skill->slug]) }}"
                   class="decoration-primary decoration-2 underline-offset-3 hover:underline">{{ $skill->name }}</a>
            @else
                <span>{{ $skill->name }}</span>
            @endif
            @if (! $loop->last)<span class="text-ink-muted" aria-hidden="true"> / </span>@endif
        @endforeach
        @if ($rest > 0)
            <span class="text-ink-muted"> +{{ $rest }}</span>
        @endif
    </p>
@endif
