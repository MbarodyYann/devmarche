<x-layouts.app :title="$title">
    <h1 class="mb-6 text-2xl sm:text-3xl">{{ $title }}</h1>
    <x-empty-state :title="$message" :action="route('devs.index')" action-label="Découvrir les devs">
        Cette rubrique arrive très bientôt. En attendant, faites connaissance avec la communauté.
    </x-empty-state>
</x-layouts.app>
