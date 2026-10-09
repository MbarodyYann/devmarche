@props(['title', 'subtitle' => null])

<div class="mx-auto w-full max-w-md">
    <div class="mb-6">
        <h1 class="text-3xl sm:text-4xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-3 text-ink-muted">{{ $subtitle }}</p>
        @endif
    </div>
    <x-block padding="p-5 sm:p-7" main>
        {{ $slot }}
    </x-block>
    @isset($footer)
        <p class="mt-6 text-ink-muted">{{ $footer }}</p>
    @endisset
</div>
