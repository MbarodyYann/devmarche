@props(['label', 'name', 'hint' => null, 'optional' => false])

{{-- Champ de formulaire : libellé au-dessus, aide, erreur sous le champ en danger. --}}
<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    <label for="{{ $name }}" class="flex items-baseline justify-between gap-2 font-semibold text-ink">
        <span>{{ $label }}</span>
        @if ($optional)
            <span class="text-sm font-normal text-ink-muted">Facultatif</span>
        @endif
    </label>
    {{ $slot }}
    @if ($hint)
        <p id="{{ $name }}-hint" class="text-sm text-ink-muted">{{ $hint }}</p>
    @endif
    @error($name)
        <p id="{{ $name }}-error" class="text-sm font-medium text-danger">{{ $message }}</p>
    @enderror
</div>
