<x-layouts.app :title="$title">
    <x-page-intro eyebrow="Questions-réponses · bientôt" title="Votre question ne se perdra plus dans un groupe WhatsApp." list>
        Posez-la en français avec les bons tags : la communauté répond et vote, et la meilleure réponse reste trouvable par tous.
    </x-page-intro>

    @guest
        <x-signup-cta class="mt-8" />
    @endguest

    <x-empty-state class="mt-12" :title="$message" :action="route('missions.index')" action-label="Voir les missions">
        Cette rubrique ouvre très bientôt. En attendant, des missions vous attendent.
    </x-empty-state>
</x-layouts.app>
