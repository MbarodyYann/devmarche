@props(['variant' => 'primary', 'href' => null, 'type' => 'button', 'size' => 'md'])

@php
    $classes = 'inline-flex items-center justify-center gap-2 rounded-btn font-semibold transition-colors '
        .'disabled:cursor-not-allowed disabled:opacity-60 '
        .($size === 'sm' ? 'min-h-9 px-3 text-sm ' : 'min-h-11 px-5 text-base ')
        .match ($variant) {
            'primary' => $size === 'sm'
                ? 'bg-primary-strong text-on-primary font-bold hover:bg-primary'
                : 'bg-primary text-on-primary font-bold hover:bg-primary-strong',
            'secondary' => 'border border-secondary text-secondary hover:bg-secondary-soft',
            'danger' => 'border border-danger text-danger hover:bg-danger hover:text-on-primary',
            default => 'text-primary-strong hover:bg-primary-soft',
        };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
