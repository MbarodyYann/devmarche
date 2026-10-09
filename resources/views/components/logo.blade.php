@props(['mark' => true, 'compact' => false])

{{-- Logo : symbole officiel + « DevMarché » en tracés Newsreader 600 (accent du é en ambre). --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
    @if ($mark)
        <x-logo-mark class="size-9" />
    @endif
    {{-- compact : le nom disparaît sous 1 024 px (symbole seul), pour laisser la place au CTA visiteur. --}}
    <svg @class(['h-6 w-auto', 'max-lg:hidden' => $compact]) viewBox="{{ App\Support\Brand::WORDMARK_VIEWBOX }}" role="img" aria-label="DevMarché" xmlns="http://www.w3.org/2000/svg">
        <path class="fill-ink" d="{{ App\Support\Brand::WORDMARK_BODY }}"/>
        <path class="fill-secondary-400" d="{{ App\Support\Brand::WORDMARK_ACCENT }}"/>
    </svg>
</span>
