@props(['variant' => 'full'])

{{-- Symbole officiel (08-design.md, « Logo »). Variantes : full (symbole complet),
     small (fenêtre et </> seuls, pour les petites tailles), mono (une seule couleur : currentColor).
     Tracés : App\Support\Brand (source unique, partagée avec x-cauri et brand:export). --}}
@php
    use App\Support\Brand;
    $small = $variant === 'small';
    $mono = $variant === 'mono';
    $uid = 'dm-'.Str::random(6);
@endphp

<svg {{ $attributes->merge(['class' => 'size-8 shrink-0']) }} viewBox="{{ $small ? Brand::FAVICON_VIEWBOX : Brand::MARK_VIEWBOX }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
    @if ($mono)
        <mask id="{{ $uid }}">
            <rect x="0" y="0" width="100" height="100" fill="#fff"/>
            <g fill="#000"><circle cx="24.4" cy="26.2" r="2.1"/><circle cx="31.1" cy="26.2" r="2.1"/><circle cx="37.7" cy="26.2" r="2.1"/></g>
            <path d="M17 30.5h60" stroke="#000" stroke-width="1.2"/>
            <path d="{{ Brand::CHEVRONS }} {{ Brand::SLASH }}" fill="none" stroke="#000" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="{{ Brand::CAURI_OUTER }}" transform="{{ Brand::CAURI_TRANSFORM }}" fill="#000" stroke="#000" stroke-width="1.4"/>
        </mask>
        <path d="{{ Brand::WINDOW }}" fill="currentColor" mask="url(#{{ $uid }})"/>
        <g transform="{{ Brand::CAURI_TRANSFORM }}" fill="currentColor">
            <path fill-rule="evenodd" d="{{ Brand::CAURI_OUTER }}{{ Brand::CAURI_SHELL }}"/>
            <path d="{{ Brand::CAURI_APERTURE }}"/>
        </g>
    @else
        <path class="fill-secondary-400" d="{{ Brand::WINDOW }}"/>
        <path class="fill-primary-600" d="{{ Brand::WINDOW_BODY }}"/>
        @unless ($small)
            <g class="fill-white"><circle cx="24.4" cy="26.2" r="2.1"/><circle cx="31.1" cy="26.2" r="2.1"/><circle cx="37.7" cy="26.2" r="2.1"/></g>
        @endunless
        <path class="stroke-white" d="{{ Brand::CHEVRONS }}" fill="none" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"/>
        <path class="stroke-secondary-400" d="{{ Brand::SLASH }}" fill="none" stroke-width="3.4" stroke-linecap="round"/>
        @unless ($small)
            <g transform="{{ Brand::CAURI_TRANSFORM }}">
                {{-- marge de la couleur du fond, pour détacher le cauri de la fenêtre --}}
                <path class="fill-bg stroke-bg" d="{{ Brand::CAURI_OUTER }}" stroke-width="1.4"/>
                <path class="fill-secondary-400" d="{{ Brand::CAURI_OUTER }}"/>
                <path class="fill-white" d="{{ Brand::CAURI_SHELL }}"/>
                <path class="fill-secondary-400" d="{{ Brand::CAURI_APERTURE }}"/>
            </g>
        @endunless
    @endif
</svg>
