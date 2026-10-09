@props(['skills', 'limit' => null, 'link' => true])

{{-- Compétences en puces neutres. --}}
@php
    $visible = $limit ? $skills->take($limit) : $skills;
    $rest = $skills->count() - $visible->count();
@endphp

@if ($skills->isNotEmpty())
    <ul {{ $attributes->merge(['class' => 'flex flex-wrap gap-1.5']) }}>
        @foreach ($visible as $skill)
            <li>
                @if ($link)
                    <a href="{{ route('devs.index', ['competence' => $skill->slug]) }}"
                       class="inline-flex rounded-[6px] bg-bg-alt px-2.5 py-1 text-sm font-medium text-ink-soft ring-1 ring-line transition hover:text-primary-600 hover:ring-primary-300 dark:hover:text-primary-200 dark:hover:ring-primary-700"><span class="sr-only">Devs en </span>{{ $skill->name }}</a>
                @else
                    <x-chip tone="neutral">{{ $skill->name }}</x-chip>
                @endif
            </li>
        @endforeach
        @if ($rest > 0)
            <li><x-chip tone="neutral">+{{ $rest }}</x-chip></li>
        @endif
    </ul>
@endif
