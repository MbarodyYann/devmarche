<div>
    {{-- Halos d'arrière-plan (2 maximum par page) --}}
    <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[520px] overflow-hidden" aria-hidden="true">
        <div class="halo -left-24 top-10 size-80 bg-primary-200 dark:bg-primary-900"></div>
        <div class="halo -right-24 top-24 size-80 bg-secondary-200 dark:bg-secondary-800"></div>
    </div>

    <x-page-intro eyebrow="La communauté" title="Les devs d'Afrique francophone">
        Trouvez le bon profil près de chez vous, par pays, ville ou compétence.
    </x-page-intro>

    {{-- Barre d'outils : compteur à gauche, disponibilité en contrôle segmenté à droite --}}
    @php($total = $this->devs->total())
    <div class="mt-12 flex flex-col gap-3 sm:mt-16 sm:flex-row sm:items-center sm:justify-between">
        <span class="inline-flex w-fit items-center gap-2 rounded-[10px] bg-bg-alt px-3.5 py-2 text-sm font-medium text-ink ring-1 ring-line">
            <span class="size-1.5 rounded-full bg-primary-500" aria-hidden="true"></span>
            <span wire:loading.remove>
                {{ $total }} {{ $total > 1 ? 'devs' : 'dev' }} {{ $this->hasFilters ? ($total > 1 ? 'trouvés' : 'trouvé') : ($total > 1 ? 'inscrits' : 'inscrit') }}
            </span>
            <span wire:loading>Recherche…</span>
        </span>
        <x-segmented label="Disponibilité" model="availability" :options="['' => 'Tous'] + $availabilities" />
    </div>

    {{-- Filtres --}}
    <x-card padding="p-4 sm:p-5" class="mt-4">
        <form wire:submit.prevent role="search" aria-label="Filtrer les devs" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.5fr)_repeat(3,minmax(0,1fr))] lg:items-end">
            <div class="sm:col-span-2 lg:col-span-1">
                <label for="filtre-q" class="mb-1.5 block text-sm font-medium text-ink">Recherche</label>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-ink-muted" />
                    <x-input name="q" id="filtre-q" type="search" wire:model.live.debounce.400ms="q" placeholder="Nom, titre, techno…" class="pl-9" />
                </div>
            </div>
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
        </form>
        @if ($this->hasFilters)
            <div class="mt-3 flex justify-end">
                <x-button variant="ghost" size="sm" wire:click="resetFilters">Effacer les filtres</x-button>
            </div>
        @endif
    </x-card>

    <div wire:loading.class="opacity-60" class="mt-6 transition-opacity">
        @if ($this->devs->isEmpty())
            <x-empty-state title="Aucun dev ne correspond à ces filtres.">
                Essayez une autre ville, ou élargissez à tous les pays.
                <div class="mt-6">
                    <x-button variant="secondary" wire:click="resetFilters">Voir tous les devs</x-button>
                </div>
            </x-empty-state>
        @else
            {{-- Ordinateur : tableau façon « Every feature, ticked » --}}
            <div class="hidden rounded-card border border-dashed border-line p-2 lg:block">
                <table class="w-full overflow-hidden rounded-[18px] bg-surface text-left shadow-card">
                    <thead>
                        <tr class="border-b border-line text-sm text-ink-muted">
                            <th scope="col" class="px-5 py-3 font-medium">Dev</th>
                            <th scope="col" class="px-4 py-3 font-medium">Lieu</th>
                            <th scope="col" class="px-4 py-3 font-medium">Compétences</th>
                            <th scope="col" class="px-4 py-3 font-medium">Disponibilité</th>
                            <th scope="col" class="px-5 py-3 text-right font-medium">Cauris</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($this->devs as $dev)
                            <tr wire:key="dev-{{ $dev->id }}" class="group relative transition hover:bg-bg-alt">
                                <td class="px-5 py-4">
                                    <a href="{{ route('devs.show', $dev) }}" class="flex items-center gap-3 after:absolute after:inset-0">
                                        <x-avatar :user="$dev" size="sm" />
                                        <span class="min-w-0">
                                            <span class="flex items-center gap-2 font-semibold text-ink group-hover:text-primary-600 dark:group-hover:text-primary-400">
                                                {{ $dev->name }}
                                                @if ($dev->isNewMember())
                                                    <x-chip tone="primary" class="px-2! py-0.5! text-xs!">Nouveau</x-chip>
                                                @endif
                                            </span>
                                            @if ($dev->profile?->headline)
                                                <span class="block max-w-64 truncate text-sm text-ink-muted">{{ $dev->profile->headline }}</span>
                                            @endif
                                        </span>
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-ink-soft">
                                    {{ $dev->profile?->flag() }} {{ $dev->profile?->city }}
                                </td>
                                <td class="px-4 py-4"><x-skills :skills="$dev->skills" :limit="3" :link="false" /></td>
                                <td class="px-4 py-4">
                                    @if ($dev->profile)
                                        <x-chip :tone="$dev->profile->availability->tone()" dot>{{ $dev->profile->availability->label() }}</x-chip>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right"><x-reputation :points="$dev->reputation" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile et tablette : cartes --}}
            <ul class="grid gap-4 sm:grid-cols-2 lg:hidden">
                @foreach ($this->devs as $dev)
                    <li wire:key="dev-card-{{ $dev->id }}"><x-dev-card :dev="$dev" /></li>
                @endforeach
            </ul>

            <div class="mt-8">
                {{ $this->devs->links() }}
            </div>
        @endif
    </div>
</div>
