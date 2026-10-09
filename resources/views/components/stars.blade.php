@props(['value', 'size' => 'size-4'])

{{-- Note en étoiles (lecture seule) : étoiles pleines en ambre, vides en contour. --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5']) }} role="img" aria-label="{{ number_format((float) $value, 1, ',', '') }} sur 5">
    @foreach (range(1, 5) as $star)
        <x-icon name="star" @class([$size, 'fill-current text-secondary-400' => $star <= round($value), 'text-primary-200 dark:text-primary-700' => $star > round($value)]) />
    @endforeach
</span>
