@props(['href' => null, 'active' => false])

@php
    $classes = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium '
        .($active ? 'bg-secondary text-on-primary' : 'bg-secondary-soft text-secondary')
        .($href ? ' hover:ring-1 hover:ring-secondary' : '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
@endif
