@props(['options', 'model', 'label'])

{{-- Contrôle segmenté façon « Monthly / Yearly », lié à une propriété Livewire. --}}
<div role="radiogroup" aria-label="{{ $label }}" {{ $attributes->merge(['class' => 'inline-flex max-w-full overflow-x-auto rounded-[10px] bg-bg-alt p-1 ring-1 ring-line [scrollbar-width:none] [&::-webkit-scrollbar]:hidden']) }}>
    @foreach ($options as $value => $optionLabel)
        <label class="relative shrink-0 cursor-pointer">
            <input type="radio" value="{{ $value }}" wire:model.live="{{ $model }}" class="peer sr-only">
            <span class="block whitespace-nowrap rounded-[7px] px-2 py-2 text-[13px] max-[359px]:px-1.5 max-[359px]:text-xs sm:px-3.5 sm:text-sm font-medium text-ink-muted transition peer-checked:bg-surface peer-checked:text-ink peer-checked:shadow-soft peer-focus-visible:outline-2 peer-focus-visible:outline-primary-500 hover:text-ink">{{ $optionLabel }}</span>
        </label>
    @endforeach
</div>
