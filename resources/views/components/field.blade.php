@props(['label', 'name', 'hint' => null, 'optional' => false])

{{-- Champ : libellé en étiquette mono au-dessus, aide, erreur sous le champ. --}}
<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    <label for="{{ $name }}" class="etiquette flex items-baseline justify-between gap-2 font-bold text-ink">
        <span>{{ $label }}</span>
        @if ($optional)
            <span class="font-normal text-ink-muted">Facultatif</span>
        @endif
    </label>
    {{ $slot }}
    @if ($hint)
        <p id="{{ $name }}-hint" class="text-sm text-ink-muted">{{ $hint }}</p>
    @endif
    @error($name)
        <p id="{{ $name }}-error" class="text-sm font-bold text-danger">{{ $message }}</p>
    @enderror
</div>
