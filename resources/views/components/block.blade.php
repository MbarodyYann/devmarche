@props(['padding' => 'p-5', 'main' => false])

{{-- Bloc : bordure d'encre de 2 px, coins de 4 px. Seul l'élément principal de la page a une ombre dure. --}}
<div {{ $attributes->class(["rounded-block border-2 border-ink bg-surface $padding", 'shadow-hard' => $main]) }}>
    {{ $slot }}
</div>
