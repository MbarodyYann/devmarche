@props(['label', 'name', 'hint' => null, 'optional' => false])

{{-- Champ : libellé au-dessus, aide, erreur sous le champ (icône + texte, jamais la couleur seule). --}}
<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    <label for="{{ $name }}" class="flex items-baseline justify-between gap-2 text-sm font-medium text-ink">
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
        <p id="{{ $name }}-error" class="flex items-start gap-1.5 text-sm font-medium text-primary-700 dark:text-primary-300">
            <x-icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}
        </p>
    @enderror
</div>
