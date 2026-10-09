@props(['name', 'type' => 'text', 'value' => null])

<input type="{{ $type }}" name="{{ $name }}" id="{{ $attributes->get('id', $name) }}"
       @if ($type !== 'password' && $type !== 'file') value="{{ old($name, $value) }}" @endif
       @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
       {{ $attributes->except('id')->class([
           'block w-full min-h-11 rounded-field border bg-surface px-3.5 text-base text-ink placeholder:text-ink-muted/70',
           'transition focus:border-secondary-500 focus:outline-none focus:ring-3 focus:ring-secondary-200 dark:focus:ring-secondary-800',
           'border-primary-500' => $errors->has($name),
           'border-line' => ! $errors->has($name),
       ]) }}>
