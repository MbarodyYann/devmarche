<x-layouts.app title="Modifier mon profil">
    @php($profile = $dev->profile)

    <div class="mx-auto max-w-2xl">
        <a href="{{ route('devs.show', $dev) }}" class="mb-4 inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-ink">
            <x-icon name="arrow-left" class="size-4" /> Retour au profil
        </a>
        <p class="eyebrow mb-3">Votre vitrine</p>
        <h1 class="text-[40px] sm:text-5xl">Modifier mon profil</h1>
        <p class="mt-3 text-ink-soft">Un profil complet attire plus de clients et de coéquipiers.</p>

        <x-card class="mt-8">
            <form method="post" action="{{ route('profile.update', $dev) }}" enctype="multipart/form-data" class="space-y-6" novalidate>
                @csrf
                @method('put')

                <div class="flex items-center gap-4">
                    <x-avatar :user="$dev" size="lg" />
                    <x-field label="Photo" name="avatar" hint="JPG, PNG ou WebP, 4 Mo maximum. Recadrée en carré." optional class="flex-1">
                        <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/webp"
                               class="block w-full text-sm text-ink-muted file:mr-3 file:min-h-10 file:rounded-btn file:border file:border-line file:bg-bg-alt file:px-4 file:font-semibold file:text-ink file:shadow-soft hover:file:bg-surface">
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

                <fieldset class="space-y-2">
                    <legend class="flex w-full items-baseline justify-between text-sm font-medium text-ink">
                        <span>Langues parlées</span><span class="font-normal text-ink-muted">Facultatif · 6 au maximum</span>
                    </legend>
                    @php($spoken = old('languages', $profile->languages ?? []))
                    <div class="flex flex-wrap gap-2">
                        @foreach ($languageOptions as $code => $label)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="languages[]" value="{{ $code }}" @checked(in_array($code, $spoken, true)) class="peer sr-only">
                                <span class="inline-flex min-h-10 items-center rounded-[8px] border border-line bg-bg-alt px-3 text-sm font-medium text-ink-soft transition peer-checked:border-primary-600 peer-checked:bg-primary-50 peer-checked:text-primary-700 peer-focus-visible:outline-2 peer-focus-visible:outline-primary-500 dark:peer-checked:border-secondary-400 dark:peer-checked:bg-secondary-950 dark:peer-checked:text-secondary-300">{{ ucfirst($label) }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('languages')
                        <p class="flex items-start gap-1.5 text-sm font-medium text-secondary-800 dark:text-secondary-300"><x-icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p>
                    @enderror
                </fieldset>

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

                <div class="flex flex-col-reverse items-center gap-3 border-t border-line pt-6 sm:flex-row sm:justify-end">
                    <x-button :href="route('devs.show', $dev)" variant="secondary">Annuler</x-button>
                    <x-button type="submit">Enregistrer</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
