@props(['name', 'value' => null, 'rows' => 4])

<textarea name="{{ $name }}" id="{{ $attributes->get('id', $name) }}" rows="{{ $rows }}"
          @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
          {{ $attributes->except('id')->class([
              'block w-full rounded-field border bg-surface px-3.5 py-2.5 text-base text-ink placeholder:text-ink-muted/70',
              'transition focus:border-primary-500 focus:outline-none focus:ring-3 focus:ring-primary-200 dark:focus:ring-primary-800',
              'border-secondary-500' => $errors->has($name),
              'border-line' => ! $errors->has($name),
          ]) }}>{{ old($name, $value) }}</textarea>
