{{-- Cauri de réputation : exactement le cauri du logo (App\Support\Brand), droit.
     Contour et ouverture en currentColor (latérite par défaut), coquille blanche.
     Sans classe fournie : 16 px en latérite ; sinon, la classe passée remplace celle par défaut. --}}
<svg class="{{ $attributes->get('class', 'size-4 text-primary-500 dark:text-primary-400') }}" {{ $attributes->except('class') }} viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
    <path fill="currentColor" d="{{ App\Support\Brand::CAURI_OUTER }}"/>
    <path class="fill-white" d="{{ App\Support\Brand::CAURI_SHELL }}"/>
    <path fill="currentColor" d="{{ App\Support\Brand::CAURI_APERTURE }}"/>
</svg>
