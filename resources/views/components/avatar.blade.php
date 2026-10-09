@props(['user', 'size' => 'md'])

{{-- Avatar « pagne » : carré, bordure d'encre, motif bogolan choisi selon l'identifiant
     (4 motifs × 3 couples de couleurs). Les photos sont carrées elles aussi. --}}
@php
    $dimensions = match ($size) {
        'sm' => 'size-9',
        'lg' => 'size-28 sm:size-32',
        default => 'size-14',
    };
    $url = $user->relationLoaded('profile') ? $user->profile?->avatarUrl() : null;
    $patterns = ['losanges', 'chevrons', 'points', 'zigzags'];
    $pairs = [
        ['bg-bg', 'bg-primary'],         // latérite sur sable
        ['bg-accent', 'bg-secondary'],   // indigo sur or
        ['bg-primary', 'bg-shadow'],     // encre sur latérite
    ];
    // Hachage de l'identifiant : des voisins dans la liste ne se ressemblent pas.
    $hash = crc32('pagne-'.$user->id);
    $pattern = $patterns[$hash % 4];
    [$ground, $ink] = $pairs[intdiv($hash, 4) % 3];
@endphp

@if ($url)
    <img src="{{ $url }}" alt="" width="256" height="256" loading="lazy"
         {{ $attributes->merge(['class' => "$dimensions shrink-0 rounded-field border-2 border-ink object-cover bg-surface"]) }}>
@else
    <span {{ $attributes->merge(['class' => "$dimensions relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-field border-2 border-ink $ground"]) }}
          aria-hidden="true">
        <span class="pagne absolute inset-0 {{ $ink }}" style="mask-image: url('/images/pagne-{{ $pattern }}.svg'); -webkit-mask-image: url('/images/pagne-{{ $pattern }}.svg')"></span>
        <span @class([
            'relative border-2 border-ink bg-surface leading-none text-ink',
            'px-1 py-0.5 font-mono text-xs font-bold' => $size === 'sm',
            'px-1.5 py-1 font-mono text-sm font-bold' => $size === 'md',
            'px-2.5 pb-1 pt-2 font-display text-3xl' => $size === 'lg',
        ])>{{ $user->initials() }}</span>
    </span>
@endif
