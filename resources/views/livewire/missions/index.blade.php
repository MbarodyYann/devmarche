<div>
    {{-- En-tête compact dans le layout (pageHero). La carte de filtres chevauche le bas du bandeau. --}}
    @php($activeFilters = collect([$country, $budgetMin, $budgetMax])->filter()->count() + ($sort !== 'recentes' ? 1 : 0))
    <x-card padding="p-3 sm:p-5" class="relative z-10 -mt-6 sm:-mt-10"
            x-data="{ open: false, desktop: window.matchMedia('(min-width: 640px)').matches }"
            x-init="window.matchMedia('(min-width: 640px)').addEventListener('change', e => desktop = e.matches)">
        <div class="mb-3 flex flex-col gap-3 border-b border-line pb-3 sm:flex-row sm:items-center sm:justify-between">
            @php($total = $this->missions->total())
            <p class="text-sm font-medium text-ink">
                <span wire:loading.remove>{{ $total }} {{ $total > 1 ? 'missions' : 'mission' }}@if ($categoryLabel = App\Support\Categories::find($category)['label'] ?? null) · {{ $categoryLabel }} <button type="button" wire:click="$set('category', '')" class="ml-1 font-semibold text-primary-600 underline-offset-4 hover:underline dark:text-primary-300">toutes les catégories</button>@endif@if ($forMe) · selon vos compétences <button type="button" wire:click="$set('forMe', false)" class="ml-1 font-semibold text-primary-600 underline-offset-4 hover:underline dark:text-primary-300">voir toutes</button>@endif</span>
                <span wire:loading>Recherche…</span>
            </p>
            <x-segmented label="Statut" model="status" :options="$statuses" />
        </div>
        <form wire:submit.prevent role="search" aria-label="Filtrer les missions" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_minmax(0,1.3fr)_minmax(0,1fr)] lg:items-end">
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                <div class="min-w-0 flex-1">
                    <label for="filtre-competence" class="mb-1.5 block text-sm font-medium text-ink max-sm:sr-only">Compétence</label>
                    <x-select name="competence" id="filtre-competence" wire:model.live="skill"
                              :options="$this->skillOptions->pluck('name', 'slug')->all()" placeholder="Toutes les compétences" />
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
                    <label for="filtre-pays" class="mb-1.5 block text-sm font-medium text-ink">Lieu</label>
                    <x-select name="pays" id="filtre-pays" wire:model.live="country" :options="$this->countryOptions" placeholder="Partout" />
                </div>
                <fieldset>
                    <legend class="mb-1.5 block text-sm font-medium text-ink">Budget (FCFA)</legend>
                    <div class="flex items-center gap-2">
                        <label for="filtre-budget-min" class="sr-only">Budget minimum</label>
                        <x-input name="budget_min" id="filtre-budget-min" inputmode="numeric" wire:model.live.debounce.500ms="budgetMin" placeholder="Min." />
                        <span class="text-ink-muted" aria-hidden="true">–</span>
                        <label for="filtre-budget-max" class="sr-only">Budget maximum</label>
                        <x-input name="budget_max" id="filtre-budget-max" inputmode="numeric" wire:model.live.debounce.500ms="budgetMax" placeholder="Max." />
                    </div>
                </fieldset>
                <div>
                    <label for="filtre-tri" class="mb-1.5 block text-sm font-medium text-ink">Trier par</label>
                    <x-select name="tri" id="filtre-tri" wire:model.live="sort" :options="['recentes' => 'Plus récentes', 'budget' => 'Budget le plus élevé']" />
                </div>
            </div>
        </form>
        @if ($this->hasFilters)
            <div class="mt-3 flex justify-end">
                <x-button variant="ghost" size="sm" wire:click="resetFilters">Effacer les filtres</x-button>
            </div>
        @endif
    </x-card>

    <div wire:loading.class="opacity-60" class="mt-6 transition-opacity">
        @if ($this->missions->isEmpty())
            <x-empty-state title="Aucune mission ne correspond… pour l'instant.">
                Élargissez à tous les pays ou à toutes les compétences.
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <x-button variant="secondary" wire:click="resetFilters">Voir toutes les missions</x-button>
                </div>
            </x-empty-state>
        @else
            <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->missions as $mission)
                    <li wire:key="mission-{{ $mission->id }}" class="min-w-0"><x-mission-card :mission="$mission" /></li>
                @endforeach
            </ul>

            <div class="mt-8">
                {{ $this->missions->links() }}
            </div>
        @endif
    </div>
</div>
