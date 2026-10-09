@props(['name', 'value' => null, 'rows' => 4])

<textarea name="{{ $name }}" id="{{ $attributes->get('id', $name) }}" rows="{{ $rows }}"
          @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
          {{ $attributes->except('id')->class([
              'block w-full rounded-btn border bg-surface px-3.5 py-2.5 text-base text-ink placeholder:text-ink-muted/70',
              'focus:border-secondary focus:outline-none focus:ring-2 focus:ring-secondary/25',
              'border-danger' => $errors->has($name),
              'border-border' => ! $errors->has($name),
          ]) }}>{{ old($name, $value) }}</textarea>
