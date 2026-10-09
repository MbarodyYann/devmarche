@props(['variant' => 'primary', 'href' => null, 'type' => 'button', 'size' => 'md'])

{{-- primary : aplat indigo cobalt (ambre en sombre) ; accent : aplat ambre, une fois par écran ;
     secondary : clair à bordure fine ; ghost : lien. --}}
@php
    // Un « hidden » passé en classe (ex. « hidden sm:inline-flex ») doit l'emporter sur l'affichage par défaut.
    $display = preg_match('/(^|\s)hidden(\s|$)/', (string) $attributes->get('class')) ? '' : 'inline-flex ';
    $classes = $display.'items-center justify-center gap-2 rounded-btn font-semibold whitespace-nowrap transition '
        .'disabled:cursor-not-allowed disabled:opacity-60 '
        .match ($size) {
            'sm' => 'min-h-10 px-3.5 text-sm ',
            'lg' => 'min-h-12 px-6 text-base ',
            default => 'min-h-11 px-4 text-[15px] ',
        }
        .match ($variant) {
            'primary' => 'btn-primary',
            'accent' => 'btn-accent',
            'secondary' => 'border border-line bg-bg-alt text-ink shadow-soft hover:bg-surface',
            'danger' => 'border border-secondary-500 bg-secondary-50 text-secondary-800 hover:bg-secondary-100 dark:border-secondary-700 dark:bg-secondary-950 dark:text-secondary-300',
            default => 'text-primary-600 hover:text-primary-700 hover:underline underline-offset-4 dark:text-primary-300',
        };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
