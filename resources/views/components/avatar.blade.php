@props(['user', 'size' => 'md'])

@php
    $dimensions = match ($size) {
        'sm' => 'size-8 text-sm',
        'lg' => 'size-24 text-2xl',
        default => 'size-12 text-base',
    };
    $url = $user->relationLoaded('profile') ? $user->profile?->avatarUrl() : null;
@endphp

@if ($url)
    <img src="{{ $url }}" alt="" width="256" height="256" loading="lazy"
         {{ $attributes->merge(['class' => "$dimensions shrink-0 rounded-full object-cover bg-secondary-soft"]) }}>
@else
    <span {{ $attributes->merge(['class' => "$dimensions inline-flex shrink-0 items-center justify-center rounded-full bg-secondary-soft font-display font-semibold text-secondary"]) }}
          aria-hidden="true">{{ $user->initials() }}</span>
@endif
