<div class="mx-auto max-w-2xl">
    <a href="{{ route('questions.index') }}" class="mb-4 inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-ink">
        <x-icon name="arrow-left" class="size-4" /> Retour aux questions
    </a>
    <p class="eyebrow mb-3">Poser ma question</p>
    <h1 class="text-[40px] sm:text-5xl">Votre question ne se perdra plus dans un groupe WhatsApp.</h1>
    <p class="mt-3 text-ink-soft">Posez-la en français avec les bons tags : la communauté répond et vote, et la meilleure réponse reste trouvable par tous.</p>

    <x-card class="mt-8">
        <form wire:submit="save" class="space-y-6" novalidate>
            <x-field label="Titre" name="title" hint="Résumez le problème en une phrase, comme si vous le cherchiez sur Google.">
                <x-input name="title" wire:model="title" maxlength="150" required autofocus />
            </x-field>

            {{-- Corps en Markdown : onglets Écrire / Aperçu (aperçu rendu côté serveur, comme à l'affichage). --}}
            <div x-data="{ tab: 'ecrire' }" class="space-y-1.5">
                <div class="flex items-center justify-between gap-2">
                    <label for="body" class="text-sm font-medium text-ink">Description</label>
                    <div role="tablist" class="inline-flex rounded-[10px] bg-bg-alt p-1 ring-1 ring-line">
                        <button type="button" role="tab" x-on:click="tab = 'ecrire'" x-bind:aria-selected="(tab === 'ecrire').toString()"
                                x-bind:class="tab === 'ecrire' ? 'bg-surface text-ink shadow-soft' : 'text-ink-muted'" class="rounded-[7px] px-3 py-1.5 text-sm font-medium">Écrire</button>
                        <button type="button" role="tab" x-on:click="tab = 'apercu'; $wire.$refresh()" x-bind:aria-selected="(tab === 'apercu').toString()"
                                x-bind:class="tab === 'apercu' ? 'bg-surface text-ink shadow-soft' : 'text-ink-muted'" class="rounded-[7px] px-3 py-1.5 text-sm font-medium">Aperçu</button>
                    </div>
                </div>
                <div x-show="tab === 'ecrire'">
                    <x-textarea name="body" wire:model="body" rows="10" maxlength="10000" class="font-mono text-sm" />
                    <p class="mt-1.5 text-sm text-ink-muted">Markdown accepté : **gras**, `code`, blocs ```php … ```, listes et liens.</p>
                </div>
                <div x-show="tab === 'apercu'" x-cloak class="min-h-40 rounded-field border border-line bg-bg-alt p-4">
                    @if ($this->preview)
                        <div class="prose-dm">{!! $this->preview !!}</div>
                    @else
                        <p class="text-ink-muted">L'aperçu s'affichera ici.</p>
                    @endif
                </div>
                @error('body')
                    <p class="flex items-start gap-1.5 text-sm font-medium text-secondary-800 dark:text-secondary-300"><x-icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p>
                @enderror
            </div>

            <x-field label="Tags" name="tags" hint="5 maximum, séparés par des virgules. Ex. Laravel, Wave API" optional>
                <x-input name="tags" wire:model="tags" list="tags-suggeres" autocomplete="off" />
                <datalist id="tags-suggeres">
                    @foreach ($suggestedTags as $suggested)
                        <option value="{{ $suggested }}"></option>
                    @endforeach
                </datalist>
            </x-field>

            <div class="flex flex-col items-center gap-3 border-t border-line pt-6 sm:items-end">
                <x-button type="submit" size="lg" class="w-full sm:w-auto" wire:loading.attr="disabled">Poser ma question</x-button>
                <p class="text-sm text-ink-muted">Gratuit · Les réponses s'affichent sur la page de votre question.</p>
            </div>
        </form>
    </x-card>
</div>
