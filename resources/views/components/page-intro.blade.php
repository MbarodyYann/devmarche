@props(['eyebrow' => null, 'title', 'align' => 'center', 'list' => false, 'compact' => false])

{{-- En-tête de page : surtitre, grand titre serif, sous-titre de 2 lignes au plus.
     list : en-tête compact sur mobile pour les pages de liste (H1 de 34 px, sous-titre masqué),
     pour que les premiers résultats soient visibles sans défiler.
     compact : H1 de 34 px et texte aligné à gauche sous 768 px (titre long), centré au-dessus. --}}
<header {{ $attributes->class(['relative', 'text-center' => $align === 'center' && ! $compact, 'md:text-center' => $align === 'center' && $compact, '-mt-4 sm:mt-0' => $list]) }}>
    @if ($eyebrow)
        <p @class(['eyebrow', 'mb-1.5 sm:mb-3' => $list, 'mb-3' => ! $list])>{{ $eyebrow }}</p>
    @endif
    <h1 @class([
        'text-[34px] sm:text-[64px]' => $list || $compact,
        'text-[44px] sm:text-[64px]' => ! $list && ! $compact,
        'mx-auto max-w-3xl' => $align === 'center',
    ])>{{ $title }}</h1>
    @if (trim($slot))
        <div @class([
            'mt-5 text-lg text-ink-soft',
            'hidden sm:block' => $list,
            'mx-auto max-w-xl' => $align === 'center',
            'max-w-2xl' => $align !== 'center',
        ])>{{ $slot }}</div>
    @endif
    @isset($actions)
        <div @class(['mt-8 flex flex-wrap items-center gap-3', 'justify-center' => $align === 'center'])>{{ $actions }}</div>
    @endisset
</header>
