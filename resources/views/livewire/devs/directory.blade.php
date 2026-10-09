<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-x-6 gap-y-2">
        <h1 class="text-3xl sm:text-4xl">Les devs</h1>
        <p class="etiquette pb-1 text-ink-muted">
            <span wire:loading.remove>
                @php($total = $this->devs->total())
                {{ $total }} {{ $total > 1 ? 'devs' : 'dev' }} {{ $this->hasFilters ? ($total > 1 ? 'trouvés' : 'trouvé') : ($total > 1 ? 'inscrits' : 'inscrit') }}
            </span>
            <span wire:loading>Recherche…</span>
        </p>
    </div>

    {{-- Filtres : une bande entre deux filets. La recherche reste visible, le reste se replie sur mobile. --}}
    @php($activeFilters = collect([$country, $city, $skill, $availability])->filter()->count())
    <section aria-label="Filtrer les devs" class="filet filet-bas mb-8 py-4"
             x-data="{ open: false, desktop: window.matchMedia('(min-width: 640px)').matches }"
             x-init="window.matchMedia('(min-width: 640px)').addEventListener('change', e => desktop = e.matches)">
        <form wire:submit.prevent role="search" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.4fr)_repeat(4,minmax(0,1fr))] lg:items-end">
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                <div class="flex-1">
                    <label for="filtre-q" class="etiquette mb-1 block font-bold">Recherche</label>
                    <x-input name="q" id="filtre-q" type="search" wire:model.live.debounce.400ms="q" placeholder="Nom, titre, techno…" />
                </div>
                <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()" aria-controls="filtres-avances"
                        class="inline-flex min-h-11 items-center gap-2 rounded-btn border-2 border-ink bg-surface px-3 font-bold enfoncable sm:hidden">
                    <x-icon name="filter" class="size-4" />
                    Filtres
                    @if ($activeFilters)
                        <span class="font-mono text-sm">({{ $activeFilters }})</span>
                    @endif
                </button>
            </div>

            <div id="filtres-avances" class="contents" x-show="open || desktop" x-cloak>
                <div>
                    <label for="filtre-pays" class="etiquette mb-1 block font-bold">Pays</label>
                    <x-select name="pays" id="filtre-pays" wire:model.live="country" :options="$this->countryOptions" placeholder="Tous les pays" />
                </div>
                <div>
                    <label for="filtre-ville" class="etiquette mb-1 block font-bold">Ville</label>
                    <x-input name="ville" id="filtre-ville" list="villes" wire:model.live.debounce.400ms="city" placeholder="Toutes" autocomplete="off" />
                    <datalist id="villes">
                        @foreach ($this->cityOptions as $cityOption)
                            <option value="{{ $cityOption }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label for="filtre-competence" class="etiquette mb-1 block font-bold">Compétence</label>
                    <x-select name="competence" id="filtre-competence" wire:model.live="skill"
                              :options="$this->skillOptions->pluck('name', 'slug')->all()" placeholder="Toutes" />
                </div>
                <div>
                    <label for="filtre-dispo" class="etiquette mb-1 block font-bold">Disponibilité</label>
                    <x-select name="dispo" id="filtre-dispo" wire:model.live="availability" :options="$availabilities" placeholder="Toutes" />
                </div>
            </div>
        </form>
        @if ($this->hasFilters)
            <div class="mt-3 flex justify-end">
                <x-button variant="ghost" size="sm" wire:click="resetFilters">Effacer les filtres</x-button>
            </div>
        @endif
    </section>

    <div wire:loading.class="opacity-60" class="transition-opacity">
        @if ($this->devs->isEmpty())
            <x-empty-state title="On a regardé partout…">
                Aucun dev ne correspond à ces filtres. Essayez une autre ville, ou élargissez à tous les pays.
                <div class="mt-6">
                    <x-button variant="secondary" wire:click="resetFilters">Voir tous les devs</x-button>
                </div>
            </x-empty-state>
        @else
            {{-- Le registre : filet de 3 px en ouverture et en clôture, 1 px entre les lignes. --}}
            <ul class="filet filet-bas divide-y divide-ink">
                @foreach ($this->devs as $dev)
                    <li wire:key="dev-{{ $dev->id }}"><x-dev-row :dev="$dev" /></li>
                @endforeach
            </ul>

            <div class="mt-8">
                {{ $this->devs->links() }}
            </div>
        @endif
    </div>
</div>
