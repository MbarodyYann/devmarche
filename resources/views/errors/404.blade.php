<x-layouts.app title="Page introuvable">
    <div class="flex flex-col items-center py-10 text-center sm:py-16">
        <x-eyes size="lg" />
        <p class="etiquette mt-8 text-ink-muted">Erreur 404</p>
        <h1 class="mt-2 text-3xl sm:text-4xl">On a regardé partout…</h1>
        <p class="mt-4 max-w-md text-lg text-ink-muted">
            …mais cette page n'existe pas, ou elle a pris un autre car. Vérifiez l'adresse ou repartez de l'annuaire.
        </p>
        <x-button :href="route('devs.index')" class="mt-8">Voir les devs</x-button>
    </div>
</x-layouts.app>
