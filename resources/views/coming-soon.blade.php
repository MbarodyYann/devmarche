<x-layouts.app :title="$title">
    <x-page-intro eyebrow="Bientôt" :title="$title" />
    <x-empty-state class="mt-12" :title="$message" :action="route('devs.index')" action-label="Découvrir les devs">
        Cette rubrique arrive très bientôt. En attendant, faites connaissance avec la communauté.
    </x-empty-state>
</x-layouts.app>
