<x-layouts.app title="Page introuvable" :noindex="true">
    <div class="dots relative overflow-hidden rounded-card px-4 py-16 text-center sm:py-24">
        <p class="font-serif text-[96px] font-medium leading-none tracking-tight text-ink sm:text-[160px]">404</p>
        <h1 class="mx-auto mt-4 max-w-2xl text-[36px] sm:text-5xl">Cette page s'est perdue en route.</h1>
        <p class="mx-auto mt-4 max-w-md text-lg text-ink-soft">L'adresse a peut-être changé. Repartez de l'accueil : les missions vous y attendent.</p>
        <div class="mt-8 flex flex-col items-center gap-3">
            <x-button :href="route('home')">Retour à l'accueil</x-button>
            <a href="{{ route('missions.index') }}" class="text-[15px] text-ink-muted underline-offset-4 hover:text-ink hover:underline">Voir les missions</a>
        </div>
    </div>
</x-layouts.app>
