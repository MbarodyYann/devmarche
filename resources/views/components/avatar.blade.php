@props(['user', 'size' => 'md'])

{{-- Avatar rond : photo, ou fond uni secondary-50 avec les initiales en Newsreader. --}}
@php
    $dimensions = match ($size) {
        'xs' => 'size-8 text-sm',
        'sm' => 'size-9 text-sm',
        'lg' => 'size-24 text-3xl sm:size-28',
        default => 'size-12 text-lg',
    };
    $url = $user->relationLoaded('profile') ? $user->profile?->avatarUrl() : null;
@endphp

@if ($url)
    <img src="{{ $url }}" alt="" width="256" height="256" loading="lazy"
         {{ $attributes->merge(['class' => "$dimensions shrink-0 rounded-full object-cover ring-2 ring-surface"]) }}>
@else
    <span {{ $attributes->merge(['class' => "$dimensions inline-flex shrink-0 items-center justify-center rounded-full bg-secondary-50 font-serif font-medium text-secondary-800 ring-2 ring-surface dark:bg-secondary-950 dark:text-secondary-300"]) }}
          aria-hidden="true">{{ $user->initials() }}</span>
@endif
