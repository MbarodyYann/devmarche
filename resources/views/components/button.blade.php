@props(['variant' => 'primary', 'href' => null, 'type' => 'button', 'size' => 'md'])

{{-- Bouton peint : bordure d'encre, ombre dure, s'enfonce au clic. Le discret est un lien souligné. --}}
@php
    $base = 'inline-flex items-center justify-center gap-2 font-bold disabled:cursor-not-allowed disabled:opacity-60 '
        .($size === 'sm' ? 'min-h-9 px-3 text-sm ' : 'min-h-11 px-5 text-base ');
    $classes = $base.match ($variant) {
        'primary' => 'rounded-btn border-2 border-ink enfoncable '
            .($size === 'sm' ? 'bg-primary-strong text-on-primary hover:bg-primary' : 'bg-primary text-on-primary hover:bg-primary-strong'),
        'secondary' => 'rounded-btn border-2 border-ink bg-surface text-ink enfoncable hover:bg-accent/25',
        'danger' => 'rounded-btn border-2 border-danger bg-surface text-danger enfoncable hover:bg-danger hover:text-on-primary',
        default => 'px-1! text-primary-strong underline decoration-2 underline-offset-4 hover:decoration-primary',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
