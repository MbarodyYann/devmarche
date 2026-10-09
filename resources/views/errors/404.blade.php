<x-layouts.app title="Page introuvable">
    <div class="dots relative overflow-hidden rounded-card px-4 py-16 text-center sm:py-24">
        <p class="eyebrow mb-4">Erreur 404</p>
        <p class="font-serif text-[96px] font-medium leading-none tracking-tight text-ink sm:text-[160px]">404</p>
        <h1 class="mx-auto mt-4 max-w-2xl text-[36px] sm:text-5xl">Cette page s'est perdue en route.</h1>
        <p class="mx-auto mt-4 max-w-md text-lg text-ink-soft">Vérifiez l'adresse, ou repartez de l'annuaire des devs.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <x-button :href="route('devs.index')">Voir les devs</x-button>
            <x-button :href="route('home')" variant="secondary">Accueil</x-button>
        </div>
    </div>
</x-layouts.app>
