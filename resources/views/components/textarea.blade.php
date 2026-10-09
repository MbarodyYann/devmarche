@props(['name', 'value' => null, 'rows' => 4])

<textarea name="{{ $name }}" id="{{ $attributes->get('id', $name) }}" rows="{{ $rows }}"
          @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
          {{ $attributes->except('id')->class([
              'block w-full rounded-field border-2 bg-surface px-3.5 py-2.5 text-base text-ink placeholder:text-ink-muted/70',
              'focus:shadow-hard-primary focus:outline-none',
              'border-danger' => $errors->has($name),
              'border-ink' => ! $errors->has($name),
          ]) }}>{{ old($name, $value) }}</textarea>
