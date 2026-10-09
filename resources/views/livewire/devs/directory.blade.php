<div>
    {{-- En-tête compact dans le layout (pageHero). La carte de filtres chevauche le bas du bandeau. --}}
    @php($activeFilters = collect([$country, $city, $skill, $level, $language, $activeWeek])->filter()->count())
    <x-card padding="p-3 sm:p-5" class="relative z-10 -mt-6 sm:-mt-10"
            x-data="{ open: false, desktop: window.matchMedia('(min-width: 640px)').matches }"
            x-init="window.matchMedia('(min-width: 640px)').addEventListener('change', e => desktop = e.matches)">
        <div class="mb-3 flex flex-col gap-3 border-b border-line pb-3 sm:flex-row sm:items-center sm:justify-between">
            @php($total = $this->devs->total())
            <p class="text-sm font-medium text-ink">
                <span wire:loading.remove>{{ $total }} {{ $total > 1 ? 'devs' : 'dev' }} {{ $this->hasFilters ? ($total > 1 ? 'trouvés' : 'trouvé') : ($total > 1 ? 'inscrits' : 'inscrit') }}</span>
                <span wire:loading>Recherche…</span>
            </p>
            <x-segmented label="Disponibilité" model="availability" :options="['' => 'Tous'] + $availabilities" />
        </div>
        <form wire:submit.prevent role="search" aria-label="Filtrer les devs" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                <div class="min-w-0 flex-1">
                    <label for="filtre-q" class="mb-1.5 block text-sm font-medium text-ink max-sm:sr-only">Recherche</label>
                    <div class="relative">
                        <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-ink-muted" />
                        <x-input name="q" id="filtre-q" type="search" wire:model.live.debounce.400ms="q" placeholder="Nom, titre, techno…" class="pl-9" />
                    </div>
                </div>
                <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()" aria-controls="filtres-avances"
                        class="inline-flex min-h-11 shrink-0 items-center gap-2 rounded-field border border-line bg-bg-alt px-3 text-sm font-semibold text-ink shadow-soft sm:hidden">
                    <x-icon name="filter" class="size-4" /> Filtres
                    @if ($activeFilters)
                        <span class="inline-flex size-5 items-center justify-center rounded-full bg-primary-600 text-xs text-white dark:bg-secondary-400 dark:text-primary-950">{{ $activeFilters }}</span>
                    @endif
                </button>
            </div>
            <div id="filtres-avances" class="contents" x-show="open || desktop" x-cloak>
            <div>
                <label for="filtre-pays" class="mb-1.5 block text-sm font-medium text-ink">Pays</label>
                <x-select name="pays" id="filtre-pays" wire:model.live="country" :options="$this->countryOptions" placeholder="Tous les pays" />
            </div>
            <div>
                <label for="filtre-ville" class="mb-1.5 block text-sm font-medium text-ink">Ville</label>
                <x-input name="ville" id="filtre-ville" list="villes" wire:model.live.debounce.400ms="city" placeholder="Toutes les villes" autocomplete="off" />
                <datalist id="villes">
                    @foreach ($this->cityOptions as $cityOption)
                        <option value="{{ $cityOption }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div>
                <label for="filtre-competence" class="mb-1.5 block text-sm font-medium text-ink">Compétence</label>
                <x-select name="competence" id="filtre-competence" wire:model.live="skill"
                          :options="$this->skillOptions->pluck('name', 'slug')->all()" placeholder="Toutes" />
            </div>
            <div>
                <label for="filtre-niveau" class="mb-1.5 block text-sm font-medium text-ink">Niveau</label>
                <x-select name="niveau" id="filtre-niveau" wire:model.live="level" :options="$levels" placeholder="Tous" />
            </div>
            <div>
                <label for="filtre-langue" class="mb-1.5 block text-sm font-medium text-ink">Langue</label>
                <x-select name="langue" id="filtre-langue" wire:model.live="language" :options="$this->languageOptions" placeholder="Toutes" />
            </div>
            <label class="flex min-h-11 cursor-pointer items-center gap-2 self-end text-sm font-medium text-ink">
                <input type="checkbox" wire:model.live="activeWeek" class="size-5 rounded border-line accent-primary-600">
                Actifs cette semaine
            </label>
            </div>
        </form>
        @if ($this->hasFilters)
            <div class="mt-3 flex justify-end">
                <x-button variant="ghost" size="sm" wire:click="resetFilters">Effacer les filtres</x-button>
            </div>
        @endif
    </x-card>

    <div wire:loading.class="opacity-60" class="mt-6 transition-opacity">
        @if ($this->devs->isEmpty())
            <x-empty-state title="Aucun dev ne correspond… pour l'instant.">
                Élargissez à tous les pays, à toutes les villes ou à toutes les compétences.
                <div class="mt-6">
                    <x-button variant="secondary" wire:click="resetFilters">Voir tous les devs</x-button>
                </div>
            </x-empty-state>
        @else
            {{-- Des cartes sur tous les écrans : 1 colonne, 2 à partir de sm, 3 à partir de lg ; même hauteur par rangée. --}}
            <ul class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->devs as $dev)
                    <li wire:key="dev-{{ $dev->id }}" class="min-w-0"><x-dev-card :dev="$dev" /></li>
                @endforeach
            </ul>

            <div class="mt-8">
                {{ $this->devs->links() }}
            </div>
        @endif
    </div>
</div>
