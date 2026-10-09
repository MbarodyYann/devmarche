@props(['tone' => 'secondary', 'struck' => false])

{{-- Tampon encreur (remplace les pastilles). Tons : success, primary, secondary, muted, danger. --}}
<span {{ $attributes->class([
    'tampon',
    'text-success' => $tone === 'success',
    'text-primary-strong' => $tone === 'primary',
    'text-secondary' => $tone === 'secondary',
    'text-ink-muted' => $tone === 'muted',
    'text-danger' => $tone === 'danger',
    'line-through' => $struck,
]) }}>{{ $slot }}</span>
