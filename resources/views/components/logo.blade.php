@props(['mark' => true])

{{-- Logo : symbole officiel + « DevMarché » en tracés Newsreader 600 (accent du é en latérite). --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
    @if ($mark)
        <x-logo-mark class="size-9" />
    @endif
    <svg class="h-6 w-auto" viewBox="{{ App\Support\Brand::WORDMARK_VIEWBOX }}" role="img" aria-label="DevMarché" xmlns="http://www.w3.org/2000/svg">
        <path class="fill-ink" d="{{ App\Support\Brand::WORDMARK_BODY }}"/>
        <path class="fill-primary-500 dark:fill-primary-400" d="{{ App\Support\Brand::WORDMARK_ACCENT }}"/>
    </svg>
</span>
