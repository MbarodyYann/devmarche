@props(['name', 'label', 'value' => null])

{{-- Saisie d'une note de 1 à 5 étoiles, accessible et sans JavaScript : un groupe de boutons radio.
     Les radios sont dans l'ordre 5 → 1 et affichés de droite à gauche (flex-row-reverse), pour que
     « :checked ~ label » colore l'étoile choisie et celles avant elle. --}}
<fieldset {{ $attributes }}>
    <legend class="mb-1.5 text-sm font-medium text-ink">{{ $label }}</legend>
    <div class="star-input">
        @foreach (range(5, 1) as $stars)
            <input type="radio" id="{{ $name }}-{{ $stars }}" name="{{ $name }}" value="{{ $stars }}" @checked((int) old($name, $value) === $stars) required>
            <label for="{{ $name }}-{{ $stars }}">
                <x-icon name="star" class="size-8 fill-current" />
                <span class="sr-only">{{ $stars }} {{ $stars > 1 ? 'étoiles' : 'étoile' }} sur 5</span>
            </label>
        @endforeach
    </div>
    @error($name)
        <p class="mt-1.5 text-sm text-secondary-800 dark:text-secondary-300">{{ $message }}</p>
    @enderror
</fieldset>
