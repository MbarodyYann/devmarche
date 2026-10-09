@props(['title', 'subtitle' => null, 'eyebrow' => null, 'demo' => false])

<div class="mx-auto w-full max-w-md">
    <div class="mb-8 text-center">
        @if ($eyebrow)
            <p class="eyebrow mb-3">{{ $eyebrow }}</p>
        @endif
        <h1 class="text-[40px] sm:text-5xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-4 text-ink-soft">{{ $subtitle }}</p>
        @endif
    </div>
    @if ($demo)
        <x-demo-panel class="mb-6" />
    @endif
    <x-card padding="p-6 sm:p-8">
        {{ $slot }}
    </x-card>
    @isset($footer)
        <p class="mt-6 text-center text-ink-soft">{{ $footer }}</p>
    @endisset
</div>
