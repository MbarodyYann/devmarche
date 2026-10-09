@props(['name', 'type' => 'text', 'value' => null])

<input type="{{ $type }}" name="{{ $name }}" id="{{ $attributes->get('id', $name) }}"
       @if ($type !== 'password' && $type !== 'file') value="{{ old($name, $value) }}" @endif
       @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
       {{ $attributes->except('id')->class([
           'block w-full min-h-11 rounded-field border-2 bg-surface px-3.5 text-base text-ink placeholder:text-ink-muted/70',
           'focus:shadow-hard-primary focus:outline-none',
           'border-danger' => $errors->has($name),
           'border-ink' => ! $errors->has($name),
       ]) }}>
