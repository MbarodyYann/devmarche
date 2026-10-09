@props(['title', 'action' => null, 'actionLabel' => null])

{{-- État vide : les yeux du car rapide, une phrase humaine, une action. Jamais une page blanche. --}}
<div {{ $attributes->merge(['class' => 'relative overflow-hidden border-y-[3px] border-ink px-4 py-12 text-center']) }}>
    <div class="frise absolute inset-x-0 top-0 h-14" aria-hidden="true"></div>
    <div class="relative flex flex-col items-center pt-6">
        <x-eyes />
        <p class="mt-6 font-display text-2xl text-ink sm:text-3xl">{{ $title }}</p>
        @if (trim($slot))
            <div class="mx-auto mt-3 max-w-md text-ink-muted">{{ $slot }}</div>
        @endif
        @if ($action)
            <x-button :href="$action" variant="secondary" class="mt-6">{{ $actionLabel }}</x-button>
        @endif
    </div>
</div>
