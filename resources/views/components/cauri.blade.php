{{-- Cauri de réputation : exactement le cauri du logo (App\Support\Brand), droit.
     Contour et ouverture en ambre (currentColor, secondary-400), cernés de secondary-600 pour rester
     visibles sur fond clair ; coquille blanche. Une classe passée remplace la taille et la couleur par défaut. --}}
<svg class="{{ $attributes->get('class', 'size-4 text-secondary-400') }}" {{ $attributes->except('class') }} viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
    <path fill="currentColor" class="stroke-secondary-600" stroke-width="1" d="{{ App\Support\Brand::CAURI_OUTER }}"/>
    <path class="fill-white" d="{{ App\Support\Brand::CAURI_SHELL }}"/>
    <path fill="currentColor" d="{{ App\Support\Brand::CAURI_APERTURE }}"/>
</svg>
