@props(['user', 'size' => 'md'])

{{-- Avatar rond : photo, ou dégradé doux primary → secondary avec les initiales en Newsreader. --}}
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
    <span {{ $attributes->merge(['class' => "$dimensions inline-flex shrink-0 items-center justify-center rounded-full bg-linear-to-br from-primary-100 to-secondary-100 font-serif font-medium text-secondary-800 ring-2 ring-surface dark:from-primary-900 dark:to-secondary-900 dark:text-secondary-200"]) }}
          aria-hidden="true">{{ $user->initials() }}</span>
@endif
