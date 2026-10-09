<x-layouts.app title="Sécuriser les fonds" :noindex="true">
    <div class="mx-auto max-w-xl">
        <a href="{{ route('missions.show', $mission) }}#candidatures" class="mb-4 inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-ink">
            <x-icon name="arrow-left" class="size-4" /> Retour aux candidatures
        </a>

        <h1 class="text-[34px] sm:text-[44px]">Sécurisez les fonds, {{ $application->user->name }} peut commencer.</h1>
        <p class="mt-3 text-lg text-ink-soft">Le montant reste bloqué jusqu'à la fin de la mission : il est libéré au dev quand vous confirmez la livraison, remboursé si vous annulez.</p>

        <x-simulation-notice class="mt-6" />

        <x-card class="mt-6">
            <dl class="grid grid-cols-2 gap-4 border-b border-line pb-5">
                <div>
                    <dt class="text-sm text-ink-muted">Montant proposé</dt>
                    <dd class="mt-1 font-serif text-[28px] font-medium leading-none text-ink">{{ App\Support\Money::fcfa($application->proposed_fcfa) }} <span class="font-sans text-sm text-ink-muted">FCFA</span></dd>
                </div>
                @if ($application->delivery_days)
                    <div>
                        <dt class="text-sm text-ink-muted">Délai proposé</dt>
                        <dd class="mt-1 font-serif text-[28px] font-medium leading-none text-ink">{{ $application->delivery_days }} <span class="font-sans text-sm text-ink-muted">{{ $application->delivery_days > 1 ? 'jours' : 'jour' }}</span></dd>
                    </div>
                @endif
            </dl>

            <form method="post" action="{{ route('payments.store', [$mission, $application]) }}" class="mt-5 space-y-5" novalidate>
                @csrf
                <fieldset>
                    <legend class="mb-2 text-sm font-medium text-ink">Votre opérateur Mobile Money</legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($providers as $value => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="provider" value="{{ $value }}" class="peer sr-only" @checked(old('provider', $pending['provider'] ?? null) === $value) required>
                                <span class="inline-flex min-h-11 items-center rounded-btn border border-line bg-bg-alt px-4 text-sm font-semibold text-ink transition peer-checked:border-secondary-400 peer-checked:bg-secondary-400 peer-checked:text-primary-950 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-primary-600">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('provider')
                        <p class="mt-1.5 text-sm text-secondary-800 dark:text-secondary-300">{{ $message }}</p>
                    @enderror
                </fieldset>

                <x-field label="Numéro Mobile Money" name="phone" hint="Il n'est pas contacté : seuls les 2 premiers et les 2 derniers chiffres seront affichés.">
                    <x-input name="phone" type="tel" inputmode="tel" autocomplete="tel" :value="old('phone', $pending['phone'] ?? '')" placeholder="Ex. 77 123 45 67" required />
                </x-field>

                <x-button type="submit" class="w-full">Recevoir le code de confirmation</x-button>
            </form>
        </x-card>
    </div>
</x-layouts.app>
