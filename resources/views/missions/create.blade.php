<x-layouts.app title="Publier une mission" :noindex="true">
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('missions.index') }}" class="mb-4 inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-ink">
            <x-icon name="arrow-left" class="size-4" /> Retour aux missions
        </a>
        <h1 class="text-[40px] sm:text-5xl">Décrivez votre besoin, les devs viennent à vous.</h1>
        <p class="mt-3 text-ink-soft">Publiez gratuitement : les devs postulent avec leur prix, vous comparez leurs profils et leurs cauris, puis vous choisissez.</p>

        <x-card class="mt-8">
            <form method="post" action="{{ route('missions.store') }}" class="space-y-6" novalidate
                  x-data="{ remote: {{ old('remote') ? 'true' : 'false' }} }">
                @csrf

                <x-field label="Titre" name="title" hint="Ex. « Intégration de l'API de paiement Wave »">
                    <x-input name="title" maxlength="120" required autofocus />
                </x-field>

                <x-field label="Description" name="description" hint="Livrables attendus, délais, contraintes techniques. Plus c'est précis, meilleures sont les candidatures.">
                    <x-textarea name="description" rows="7" maxlength="5000" required />
                </x-field>

                <div class="grid gap-6 sm:grid-cols-2">
                    <x-field label="Budget (FCFA)" name="budget_fcfa" hint="Montant total en FCFA. Ex. 150 000">
                        <x-input name="budget_fcfa" inputmode="numeric" placeholder="150000" required />
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

                <div class="flex flex-col items-center gap-3 border-t border-line pt-6 sm:items-end">
                    <x-button type="submit" size="lg" class="w-full sm:w-auto">Publier ma mission</x-button>
                    <p class="text-sm text-ink-muted">Gratuit · Aucun paiement avant d'avoir choisi votre dev.</p>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
