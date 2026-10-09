@props(['title', 'action' => null, 'actionLabel' => null])

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-card border border-dashed border-border bg-surface px-6 py-12 text-center']) }}>
    <div class="frise absolute inset-x-0 top-0 h-12" aria-hidden="true"></div>
    <div class="relative">
        <p class="font-display text-xl font-semibold text-ink">{{ $title }}</p>
        @if (trim($slot))
            <div class="mx-auto mt-2 max-w-md text-ink-muted">{{ $slot }}</div>
        @endif
        @if ($action)
            <x-button :href="$action" variant="secondary" class="mt-6">{{ $actionLabel }}</x-button>
        @endif
    </div>
</div>
