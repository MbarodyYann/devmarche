@props(['name', 'options' => [], 'value' => null, 'placeholder' => null])

{{-- $options : [valeur => libellé] ou groupes [nom du groupe => [valeur => libellé]]. --}}
@php($selected = (string) old($name, $value))

<select name="{{ $name }}" id="{{ $attributes->get('id', $name) }}"
        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
        {{ $attributes->except('id')->class([
            'block w-full min-h-11 rounded-field border-2 bg-surface px-3 text-base text-ink',
            'focus:shadow-hard-primary focus:outline-none',
            'border-danger' => $errors->has($name),
            'border-ink' => ! $errors->has($name),
        ]) }}>
    @if ($placeholder !== null)
        <option value="">{{ $placeholder }}</option>
    @endif
    @foreach ($options as $key => $option)
        @if (is_array($option))
            <optgroup label="{{ $key }}">
                @foreach ($option as $optionValue => $label)
                    <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $label }}</option>
                @endforeach
            </optgroup>
        @else
            <option value="{{ $key }}" @selected($selected === (string) $key)>{{ $option }}</option>
        @endif
    @endforeach
</select>
