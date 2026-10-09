<x-layouts.app :title="$title">
    <h1 class="mb-6 text-3xl sm:text-4xl">{{ $title }}</h1>
    <x-empty-state :title="$message" :action="route('devs.index')" action-label="Découvrir les devs">
        Cette rubrique arrive très bientôt. En attendant, faites connaissance avec la communauté.
    </x-empty-state>
</x-layouts.app>
