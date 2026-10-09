<div>
    {{-- En-tête compact dans le layout (pageHero). La carte de filtres chevauche le bas du bandeau. --}}

    @php($activeFilters = collect([$tag])->filter()->count() + ($sort !== 'recentes' ? 1 : 0))
    <x-card padding="p-3 sm:p-5" class="relative z-10 -mt-6 sm:-mt-10"
            x-data="{ open: false, desktop: window.matchMedia('(min-width: 640px)').matches }"
            x-init="window.matchMedia('(min-width: 640px)').addEventListener('change', e => desktop = e.matches)">
        <div class="mb-3 flex flex-col gap-3 border-b border-line pb-3 sm:flex-row sm:items-center sm:justify-between">
            @php($total = $this->questions->total())
            <p class="text-sm font-medium text-ink">
                <span wire:loading.remove>{{ $total }} {{ $total > 1 ? 'questions' : 'question' }}</span>
                <span wire:loading>Recherche…</span>
            </p>
            <x-segmented label="Statut" model="status" :options="['toutes' => 'Toutes', 'sans-reponse' => 'Sans réponse', 'resolues' => 'Résolues']" />
        </div>
        <form wire:submit.prevent role="search" aria-label="Chercher une question" class="grid gap-3 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)] sm:items-end">
            <div class="flex items-end gap-2">
                <div class="min-w-0 flex-1">
                    <label for="filtre-q" class="mb-1.5 block text-sm font-medium text-ink max-sm:sr-only">Recherche</label>
                    <div class="relative">
                        <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-ink-muted" />
                        <x-input name="q" id="filtre-q" type="search" wire:model.live.debounce.400ms="q" placeholder="webhook Wave, USSD, Laravel en 3G…" class="pl-9" />
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
                <label for="filtre-tag" class="mb-1.5 block text-sm font-medium text-ink">Tag</label>
                <x-select name="tag" id="filtre-tag" wire:model.live="tag" :options="$this->tagOptions->pluck('name', 'slug')->all()" placeholder="Tous les tags" />
            </div>
            <div>
                <label for="filtre-tri" class="mb-1.5 block text-sm font-medium text-ink">Trier par</label>
                <x-select name="tri" id="filtre-tri" wire:model.live="sort" :options="['recentes' => 'Plus récentes', 'votes' => 'Plus votées', 'sans-reponse' => 'Sans réponse d\'abord']" />
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
        @if ($this->questions->isEmpty())
            <x-empty-state title="Personne n'a encore posé cette question.">
                Soyez le premier : la communauté répond vite, et la réponse servira à tous.
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    @auth
                        <x-button :href="route('questions.create')">Poser ma question</x-button>
                    @endauth
                    <x-button variant="secondary" wire:click="resetFilters">Voir toutes les questions</x-button>
                </div>
            </x-empty-state>
        @else
            <ul class="grid gap-4">
                @foreach ($this->questions as $question)
                    <li wire:key="question-{{ $question->id }}" class="min-w-0"><x-question-card :question="$question" /></li>
                @endforeach
            </ul>

            <div class="mt-8">
                {{ $this->questions->links() }}
            </div>
        @endif
    </div>
</div>
