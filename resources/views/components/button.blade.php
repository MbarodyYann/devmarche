@props(['variant' => 'primary', 'href' => null, 'type' => 'button', 'size' => 'md'])

{{-- primary : aplat indigo ; secondary : clair à bordure fine ; ghost : lien. --}}
@php
    $classes = 'inline-flex items-center justify-center gap-2 rounded-btn font-semibold whitespace-nowrap transition '
        .'disabled:cursor-not-allowed disabled:opacity-60 '
        .($size === 'sm' ? 'min-h-10 px-3.5 text-sm ' : 'min-h-11 px-4 text-[15px] ')
        .match ($variant) {
            'primary' => 'btn-primary',
            'secondary' => 'border border-line bg-bg-alt text-ink shadow-soft hover:bg-surface',
            'danger' => 'border border-primary-200 bg-primary-50 text-primary-700 hover:bg-primary-100 dark:border-primary-800 dark:bg-primary-950 dark:text-primary-300',
            default => 'text-primary-600 hover:text-primary-700 hover:underline underline-offset-4 dark:text-primary-400',
        };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
