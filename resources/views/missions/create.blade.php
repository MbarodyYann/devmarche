<x-layouts.app title="Publier une mission">
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('missions.index') }}" class="mb-4 inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-ink">
            <x-icon name="arrow-left" class="size-4" /> Retour aux missions
        </a>
        <p class="eyebrow mb-3">Trouvez le bon dev</p>
        <h1 class="text-[40px] sm:text-5xl">Publier une mission</h1>
        <p class="mt-3 text-ink-soft">Décrivez le besoin, fixez un budget en FCFA : les candidatures arrivent sur la fiche de la mission.</p>

        <x-card class="mt-8">
            <form method="post" action="{{ route('missions.store') }}" class="space-y-6" novalidate
                  x-data="{ remote: {{ old('remote') ? 'true' : 'false' }} }">
                @csrf

                <x-field label="Titre" name="title" hint="Ex. « Intégration de l'API de paiement Wave »">
                    <x-input name="title" maxlength="120" required autofocus />
                </x-field>

                <x-field label="Description" name="description" hint="Le contexte, ce qui est attendu, les livrables. 30 caractères minimum.">
                    <x-textarea name="description" rows="7" maxlength="5000" required />
                </x-field>

                <div class="grid gap-6 sm:grid-cols-2">
                    <x-field label="Budget (FCFA)" name="budget_fcfa" hint="5 000 FCFA minimum.">
                        <x-input name="budget_fcfa" inputmode="numeric" placeholder="150 000" required />
                    </x-field>
                    <x-field label="Échéance" name="deadline" optional>
                        <x-input name="deadline" type="date" :min="now()->addDay()->toDateString()" />
                    </x-field>
                </div>

                <div class="space-y-3">
                    <x-field label="Pays" name="country">
                        <x-select name="country" :options="$countries" :value="$defaultCountry" placeholder="Choisir un pays" x-bind:disabled="remote" />
                    </x-field>
                    <label class="inline-flex min-h-11 items-center gap-2 text-ink">
                        <input type="checkbox" name="remote" value="1" x-model="remote" @checked(old('remote')) class="size-5 rounded border-line accent-primary-600">
                        Télétravail (n'importe quel pays)
                    </label>
                </div>

                <x-field label="Compétences recherchées" name="skills" hint="Séparées par des virgules, 8 maximum. Ex. Laravel, Wave API" optional>
                    <x-input name="skills" list="competences" autocomplete="off" />
                    <datalist id="competences">
                        @foreach ($suggestedSkills as $suggestedSkill)
                            <option value="{{ $suggestedSkill }}"></option>
                        @endforeach
                    </datalist>
                </x-field>

                <div class="flex flex-col-reverse items-center gap-3 border-t border-line pt-6 sm:flex-row sm:justify-end">
                    <x-button :href="route('missions.index')" variant="secondary">Annuler</x-button>
                    <x-button type="submit">Publier la mission</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
