<div>
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl">Les devs</h1>
            <p class="mt-1 text-ink-muted">Trouvez le bon profil près de chez vous, par pays, ville ou compétence.</p>
        </div>
        <p class="text-sm text-ink-muted" wire:loading.remove>
            {{ $this->devs->total() }} {{ Str::plural('dev', $this->devs->total()) }}
        </p>
        <p class="text-sm text-ink-muted" wire:loading>Recherche…</p>
    </div>

    {{-- Filtres : la recherche reste visible, les autres filtres se replient sur mobile. --}}
    @php($activeFilters = collect([$country, $city, $skill, $availability])->filter()->count())
    <x-card padding="p-4" class="mb-6"
            x-data="{ open: false, desktop: window.matchMedia('(min-width: 640px)').matches }"
            x-init="window.matchMedia('(min-width: 640px)').addEventListener('change', e => desktop = e.matches)">
        <form wire:submit.prevent role="search" aria-label="Filtrer les devs">
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label for="filtre-q" class="mb-1 block text-sm font-semibold">Recherche</label>
                    <x-input name="q" id="filtre-q" type="search" wire:model.live.debounce.400ms="q" placeholder="Nom, titre, techno…" />
                </div>
                <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()" aria-controls="filtres-avances"
                        class="inline-flex min-h-11 items-center gap-2 rounded-btn border border-border bg-surface px-3 font-semibold text-secondary sm:hidden">
                    <x-icon name="filter" class="size-4" />
                    Filtres
                    @if ($activeFilters)
                        <span class="inline-flex size-5 items-center justify-center rounded-full bg-secondary text-sm text-on-primary">{{ $activeFilters }}</span>
                    @endif
                </button>
            </div>

            <div id="filtres-avances" class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4" x-show="open || desktop" x-cloak>
                <div>
                    <label for="filtre-pays" class="mb-1 block text-sm font-semibold">Pays</label>
                    <x-select name="pays" id="filtre-pays" wire:model.live="country" :options="$this->countryOptions" placeholder="Tous les pays" />
                </div>
                <div>
                    <label for="filtre-ville" class="mb-1 block text-sm font-semibold">Ville</label>
                    <x-input name="ville" id="filtre-ville" list="villes" wire:model.live.debounce.400ms="city" placeholder="Toutes les villes" autocomplete="off" />
                    <datalist id="villes">
                        @foreach ($this->cityOptions as $cityOption)
                            <option value="{{ $cityOption }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label for="filtre-competence" class="mb-1 block text-sm font-semibold">Compétence</label>
                    <x-select name="competence" id="filtre-competence" wire:model.live="skill"
                              :options="$this->skillOptions->pluck('name', 'slug')->all()" placeholder="Toutes" />
                </div>
                <div>
                    <label for="filtre-dispo" class="mb-1 block text-sm font-semibold">Disponibilité</label>
                    <x-select name="dispo" id="filtre-dispo" wire:model.live="availability" :options="$availabilities" placeholder="Toutes" />
                </div>
            </div>
        </form>
        @if ($this->hasFilters)
            <div class="mt-3 flex justify-end">
                <x-button variant="ghost" size="sm" wire:click="resetFilters">
                    <x-icon name="x" class="size-4" /> Effacer les filtres
                </x-button>
            </div>
        @endif
    </x-card>

    <div wire:loading.class="opacity-60" class="transition-opacity">
        @if ($this->devs->isEmpty())
            <x-empty-state title="Aucun dev ne correspond à ces filtres.">
                Essayez une autre ville, ou élargissez à tous les pays.
                <div class="mt-6">
                    <x-button variant="secondary" wire:click="resetFilters">Voir tous les devs</x-button>
                </div>
            </x-empty-state>
        @else
            <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->devs as $dev)
                    <li wire:key="dev-{{ $dev->id }}"><x-dev-card :dev="$dev" /></li>
                @endforeach
            </ul>

            <div class="mt-8">
                {{ $this->devs->links() }}
            </div>
        @endif
    </div>
</div>
