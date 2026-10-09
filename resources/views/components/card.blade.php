@props(['href' => null, 'padding' => 'p-5'])

@php
    $classes = "block rounded-card border border-border bg-surface shadow-card $padding"
        .($href ? ' transition-colors hover:border-primary-soft focus-visible:border-primary' : '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</div>
@endif
