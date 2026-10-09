@props(['title', 'subtitle' => null])

<div class="mx-auto w-full max-w-md">
    <div class="mb-6 text-center">
        <h1 class="text-2xl sm:text-3xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-2 text-ink-muted">{{ $subtitle }}</p>
        @endif
    </div>
    <x-card padding="p-6 sm:p-8">
        {{ $slot }}
    </x-card>
    @isset($footer)
        <p class="mt-6 text-center text-ink-muted">{{ $footer }}</p>
    @endisset
</div>
