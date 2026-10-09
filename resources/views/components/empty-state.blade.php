@props(['title', 'action' => null, 'actionLabel' => null])

{{-- État vide : carte sur trame de points, symbole du logo, phrase humaine, action. --}}
<div {{ $attributes->merge(['class' => 'dots relative overflow-hidden rounded-2xl border border-dashed border-line px-6 py-14 text-center sm:rounded-card']) }}>
    <x-logo-mark class="mx-auto mb-5 size-14" />
    <p class="font-serif text-2xl font-medium tracking-tight text-ink sm:text-[28px]">{{ $title }}</p>
    @if (trim($slot))
        <div class="mx-auto mt-3 max-w-md text-ink-soft">{{ $slot }}</div>
    @endif
    @if ($action)
        <x-button :href="$action" variant="secondary" class="mt-6">{{ $actionLabel }}</x-button>
    @endif
</div>
