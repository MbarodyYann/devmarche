@props(['options', 'model', 'label'])

{{-- Contrôle segmenté façon « Monthly / Yearly », lié à une propriété Livewire. --}}
<div role="radiogroup" aria-label="{{ $label }}" {{ $attributes->merge(['class' => 'inline-flex max-w-full overflow-x-auto rounded-[10px] bg-bg-alt p-1 ring-1 ring-line']) }}>
    @foreach ($options as $value => $optionLabel)
        <label class="relative cursor-pointer">
            <input type="radio" value="{{ $value }}" wire:model.live="{{ $model }}" class="peer sr-only">
            <span class="block whitespace-nowrap rounded-[7px] px-3.5 py-2 text-sm font-medium text-ink-muted transition peer-checked:bg-surface peer-checked:text-ink peer-checked:shadow-soft peer-focus-visible:outline-2 peer-focus-visible:outline-secondary-500 hover:text-ink">{{ $optionLabel }}</span>
        </label>
    @endforeach
</div>
