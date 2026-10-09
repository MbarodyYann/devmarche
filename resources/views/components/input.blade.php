@props(['name', 'type' => 'text', 'value' => null])

<input type="{{ $type }}" name="{{ $name }}" id="{{ $attributes->get('id', $name) }}"
       @if ($type !== 'password' && $type !== 'file') value="{{ old($name, $value) }}" @endif
       @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
       {{ $attributes->except('id')->class([
           'block w-full min-h-11 rounded-field border bg-surface px-3.5 text-base text-ink placeholder:text-ink-muted/70',
           'transition focus:border-primary-500 focus:outline-none focus:ring-3 focus:ring-primary-200 dark:focus:ring-primary-800',
           'border-secondary-500' => $errors->has($name),
           'border-line' => ! $errors->has($name),
       ]) }}>
