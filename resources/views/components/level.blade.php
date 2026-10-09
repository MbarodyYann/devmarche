@props(['user'])

{{-- Niveau du dev (Nouveau, Confirmé, Expert, Référence), en étiquette texte seul. Rien si aucun niveau. --}}
@if ($level = $user->level())
    <x-chip :tone="$level->tone()" title="Niveau calculé à partir des cauris et des missions terminées" {{ $attributes }}>{{ $level->label() }}</x-chip>
@endif
