@props(['eyebrow' => null, 'title', 'align' => 'center'])

{{-- En-tête de page : surtitre en dégradé, grand titre serif, sous-titre de 2 lignes au plus. --}}
<header {{ $attributes->class(['relative', 'text-center' => $align === 'center']) }}>
    @if ($eyebrow)
        <p class="eyebrow mb-3">{{ $eyebrow }}</p>
    @endif
    <h1 @class(['text-[44px] sm:text-[64px]', 'mx-auto max-w-3xl' => $align === 'center'])>{{ $title }}</h1>
    @if (trim($slot))
        <div @class(['mt-5 text-lg text-ink-soft', 'mx-auto max-w-xl' => $align === 'center', 'max-w-2xl' => $align !== 'center'])>{{ $slot }}</div>
    @endif
    @isset($actions)
        <div @class(['mt-8 flex flex-wrap items-center gap-3', 'justify-center' => $align === 'center'])>{{ $actions }}</div>
    @endisset
</header>
