<x-layouts.app title="Modifier mon profil">
    @php($profile = $dev->profile)

    <div class="mx-auto max-w-2xl">
        <a href="{{ route('devs.show', $dev) }}" class="etiquette mb-4 inline-flex min-h-11 items-center gap-1 text-ink-muted hover:text-ink">
            <x-icon name="arrow-left" class="size-4" /> Retour au profil
        </a>
        <h1 class="text-3xl sm:text-4xl">Modifier mon profil</h1>
        <p class="mt-1 text-ink-muted">Un profil complet attire plus de clients et de coéquipiers.</p>

        <x-block class="mt-6" padding="p-5 sm:p-8" main>
            <form method="post" action="{{ route('profile.update', $dev) }}" enctype="multipart/form-data" class="space-y-6" novalidate>
                @csrf
                @method('put')

                <div class="flex items-center gap-4">
                    <x-avatar :user="$dev" size="lg" />
                    <x-field label="Photo" name="avatar" hint="JPG, PNG ou WebP, 4 Mo maximum. Recadrée en carré." optional class="flex-1">
                        <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/webp"
                               class="block w-full text-sm text-ink-muted file:mr-3 file:min-h-11 file:rounded-btn file:border-2 file:border-ink file:bg-surface file:px-4 file:font-bold file:text-ink hover:file:bg-accent/25">
                    </x-field>
                </div>

                <x-field label="Nom complet" name="name">
                    <x-input name="name" :value="$dev->name" autocomplete="name" required />
                </x-field>

                <x-field label="Titre" name="headline" hint="Ex. « Dev Laravel & Flutter »" optional>
                    <x-input name="headline" :value="$profile->headline" maxlength="80" />
                </x-field>

                <x-field label="Présentation" name="bio" hint="Quelques lignes sur votre parcours et ce que vous aimez construire." optional>
                    <x-textarea name="bio" :value="$profile->bio" rows="5" maxlength="1000" />
                </x-field>

                <div class="grid gap-6 sm:grid-cols-2">
                    <x-field label="Pays" name="country">
                        <x-select name="country" :options="$countries" :value="$profile->country" required />
                    </x-field>
                    <x-field label="Ville" name="city">
                        <x-input name="city" :value="$profile->city" autocomplete="address-level2" required />
                    </x-field>
                </div>

                <x-field label="Disponibilité" name="availability">
                    <x-select name="availability" :options="$availabilities" :value="$profile->availability->value" required />
                </x-field>

                <x-field label="Compétences" name="skills" hint="Séparées par des virgules, 12 maximum. Ex. Laravel, Flutter, Wave API" optional>
                    <x-input name="skills" list="competences" :value="$dev->skills->pluck('name')->implode(', ')" autocomplete="off" />
                    <datalist id="competences">
                        @foreach ($suggestedSkills as $suggestedSkill)
                            <option value="{{ $suggestedSkill }}"></option>
                        @endforeach
                    </datalist>
                </x-field>

                <div class="grid gap-6 sm:grid-cols-2">
                    <x-field label="Lien GitHub" name="github_url" optional>
                        <x-input name="github_url" type="url" :value="$profile->github_url" placeholder="https://github.com/…" />
                    </x-field>
                    <x-field label="Portfolio" name="portfolio_url" optional>
                        <x-input name="portfolio_url" type="url" :value="$profile->portfolio_url" placeholder="https://…" />
                    </x-field>
                </div>

                <div class="flex flex-col-reverse items-center gap-4 border-t-2 border-ink pt-6 sm:flex-row sm:justify-end">
                    <x-button :href="route('devs.show', $dev)" variant="ghost">Annuler</x-button>
                    <x-button type="submit">Enregistrer</x-button>
                </div>
            </form>
        </x-block>
    </div>
</x-layouts.app>
