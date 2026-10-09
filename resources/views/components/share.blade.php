@props(['title', 'url' => null])

@php
    $url ??= url()->current();
    $whatsapp = 'https://wa.me/?text='.rawurlencode($title.' · DevMarché'."\n".$url);
@endphp

{{-- Partage : WhatsApp (le canal de nos utilisateurs) et copie du lien. Le bouton « Copier » n'existe qu'avec JS (x-cloak). --}}
<div {{ $attributes->class('flex flex-wrap items-center gap-2') }} data-share>
    <x-button :href="$whatsapp" variant="secondary" size="sm" target="_blank" rel="noopener noreferrer" data-share-whatsapp>
        <x-icon name="message" class="size-4" /> Partager sur WhatsApp
    </x-button>
    {{-- L'URL passe par data-url : les directives Blade (@js) ne sont pas compilées dans les attributs d'un composant. --}}
    <x-button type="button" variant="secondary" size="sm" x-cloak data-share-copy :data-url="$url"
              x-data="{ copied: false }"
              x-on:click="
                  const url = $el.dataset.url;
                  const done = () => { copied = true; setTimeout(() => copied = false, 2000) };
                  const fallback = () => { const field = Object.assign(document.createElement('textarea'), { value: url }); document.body.append(field); field.select(); document.execCommand('copy'); field.remove(); done() };
                  navigator.clipboard ? navigator.clipboard.writeText(url).then(done, fallback) : fallback()
              ">
        <x-icon name="copy" class="size-4" />
        <span aria-live="polite" x-text="copied ? 'Lien copié' : 'Copier le lien'">Copier le lien</span>
    </x-button>
</div>
