@props(['title', 'action' => null, 'actionLabel' => null])

{{-- État vide : carte sur trame de points, pastille cauri, phrase humaine, action. --}}
<div {{ $attributes->merge(['class' => 'dots relative overflow-hidden rounded-2xl border border-dashed border-line px-6 py-14 text-center sm:rounded-card']) }}>
    <span class="mx-auto mb-5 inline-flex size-12 items-center justify-center rounded-xl bg-linear-to-br from-secondary-700 to-primary-500 shadow-soft" aria-hidden="true">
        <x-cauri class="size-6 text-white" />
    </span>
    <p class="font-serif text-2xl font-medium tracking-tight text-ink sm:text-[28px]">{{ $title }}</p>
    @if (trim($slot))
        <div class="mx-auto mt-3 max-w-md text-ink-soft">{{ $slot }}</div>
    @endif
    @if ($action)
        <x-button :href="$action" variant="secondary" class="mt-6">{{ $actionLabel }}</x-button>
    @endif
</div>
