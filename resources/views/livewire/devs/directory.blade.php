<div>
    <x-page-intro eyebrow="La communauté" title="Les devs d'Afrique francophone" list>
        Trouvez le bon profil près de chez vous, par pays, ville ou compétence.
    </x-page-intro>

    {{-- Barre d'outils : compteur à gauche, disponibilité en contrôle segmenté à droite --}}
    @php($total = $this->devs->total())
    <div class="mt-6 flex flex-col gap-3 sm:mt-16 sm:flex-row sm:items-center sm:justify-between">
        <span class="inline-flex w-fit items-center gap-2 rounded-[10px] bg-bg-alt px-3.5 py-2 text-sm font-medium text-ink ring-1 ring-line">
            <span class="size-1.5 rounded-full bg-secondary-400" aria-hidden="true"></span>
            <span wire:loading.remove>
                {{ $total }} {{ $total > 1 ? 'devs' : 'dev' }} {{ $this->hasFilters ? ($total > 1 ? 'trouvés' : 'trouvé') : ($total > 1 ? 'inscrits' : 'inscrit') }}
            </span>
            <span wire:loading>Recherche…</span>
        </span>
        <x-segmented label="Disponibilité" model="availability" :options="['' => 'Tous'] + $availabilities" />
    </div>

    {{-- Filtres --}}
    {{-- Sur mobile, seuls la recherche et le bouton « Filtres » sont visibles : les premiers devs restent à l'écran. --}}
    @php($activeFilters = collect([$country, $city, $skill])->filter()->count())
    <x-card padding="p-3 sm:p-5" class="mt-4"
            x-data="{ open: false, desktop: window.matchMedia('(min-width: 640px)').matches }"
            x-init="window.matchMedia('(min-width: 640px)').addEventListener('change', e => desktop = e.matches)">
        <form wire:submit.prevent role="search" aria-label="Filtrer les devs" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.5fr)_repeat(3,minmax(0,1fr))] lg:items-end">
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
                                            <span class="flex items-center gap-2 font-semibold text-ink group-hover:text-primary-600 dark:group-hover:text-primary-300">
                                                {{ $dev->name }}
                                                @if ($dev->isNewMember())
                                                    <x-chip tone="warm" class="px-2! py-0.5! text-xs!">Nouveau</x-chip>
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
                    <li wire:key="dev-card-{{ $dev->id }}" class="min-w-0"><x-dev-card :dev="$dev" /></li>
                @endforeach
            </ul>

            <div class="mt-8">
                {{ $this->devs->links() }}
            </div>
        @endif
    </div>
</div>
