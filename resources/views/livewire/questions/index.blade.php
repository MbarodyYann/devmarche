<div>
    <x-page-intro eyebrow="Questions-réponses" title="Votre question ne se perdra plus dans un groupe WhatsApp." list>
        Posez-la en français avec les bons tags : la communauté répond et vote, et la meilleure réponse reste trouvable par tous.
    </x-page-intro>

    @guest
        <x-signup-cta class="mt-8" />
    @else
        <div class="mt-8 flex justify-center">
            <x-button :href="route('questions.create')" size="lg" data-cta="principal"><x-icon name="plus" class="size-4" /> Poser ma question</x-button>
        </div>
    @endguest

    @php($total = $this->questions->total())
    <div class="mt-8 flex flex-col gap-3 sm:mt-14 sm:flex-row sm:items-center sm:justify-between">
        <span class="inline-flex w-fit items-center gap-2 rounded-[10px] bg-bg-alt px-3.5 py-2 text-sm font-medium text-ink ring-1 ring-line">
            <span wire:loading.remove>{{ $total }} {{ $total > 1 ? 'questions' : 'question' }}</span>
            <span wire:loading>Recherche…</span>
        </span>
        <x-segmented label="Statut" model="status" :options="['toutes' => 'Toutes', 'sans-reponse' => 'Sans réponse', 'resolues' => 'Résolues']" />
    </div>

    <x-card padding="p-3 sm:p-5" class="mt-4">
        <form wire:submit.prevent role="search" aria-label="Chercher une question" class="grid gap-3 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)] sm:items-end">
            <div>
                <label for="filtre-q" class="mb-1.5 block text-sm font-medium text-ink">Recherche</label>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-ink-muted" />
                    <x-input name="q" id="filtre-q" type="search" wire:model.live.debounce.400ms="q" placeholder="webhook Wave, USSD, Laravel en 3G…" class="pl-9" />
                </div>
            </div>
            <div>
                <label for="filtre-tag" class="mb-1.5 block text-sm font-medium text-ink">Tag</label>
                <x-select name="tag" id="filtre-tag" wire:model.live="tag" :options="$this->tagOptions->pluck('name', 'slug')->all()" placeholder="Tous les tags" />
            </div>
            <div>
                <label for="filtre-tri" class="mb-1.5 block text-sm font-medium text-ink">Trier par</label>
                <x-select name="tri" id="filtre-tri" wire:model.live="sort" :options="['recentes' => 'Plus récentes', 'votes' => 'Plus votées', 'sans-reponse' => 'Sans réponse d\'abord']" />
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
