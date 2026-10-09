@props(['mark' => true, 'compact' => false])

{{-- Logo : symbole officiel + « DevMarché » en tracés Newsreader 600 (accent du é en ambre). --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center '.($compact ? 'gap-1.5 lg:gap-2' : 'gap-2')]) }}>
    @if ($mark)
        <x-logo-mark :class="$compact ? 'size-7 lg:size-9' : 'size-9'" />
    @endif
    {{-- compact (en-tête mobile d'un visiteur) : nom réduit sous 1 024 px pour laisser la place au CTA,
         symbole seul sous 360 px. --}}
    <svg @class(['w-auto', 'h-6' => ! $compact, 'h-[17px] max-[359px]:hidden lg:h-6' => $compact]) viewBox="{{ App\Support\Brand::WORDMARK_VIEWBOX }}" role="img" aria-label="DevMarché" xmlns="http://www.w3.org/2000/svg">
        <path class="fill-ink" d="{{ App\Support\Brand::WORDMARK_BODY }}"/>
        <path class="fill-secondary-400" d="{{ App\Support\Brand::WORDMARK_ACCENT }}"/>
    </svg>
</span>
