<x-layouts.app title="Confirmer le paiement" :noindex="true">
    <div class="mx-auto max-w-xl">
        <a href="{{ route('payments.create', [$mission, $application]) }}" class="mb-4 inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-ink">
            <x-icon name="arrow-left" class="size-4" /> Modifier l'opérateur ou le numéro
        </a>

        <h1 class="text-[34px] sm:text-[44px]">Un dernier code, et la mission démarre.</h1>
        <p class="mt-3 text-lg text-ink-soft">Recopiez le code reçu pour sécuriser {{ App\Support\Money::fcfa($application->proposed_fcfa) }} FCFA via {{ $providerLabel }}.</p>

        <x-simulation-notice class="mt-6" />

        {{-- Le « SMS » est simulé : le code est affiché ici, rien n'est envoyé. --}}
        <div class="mt-6 rounded-2xl border border-line bg-bg-alt p-4" data-simulated-sms>
            <p class="text-sm text-ink-muted">SMS simulé {{ App\Support\French::de($providerLabel) }} au {{ $maskedPhone }}</p>
            <p class="mt-1 text-ink">Votre code de confirmation DevMarché est <strong class="font-serif text-2xl font-medium tracking-[.2em]">{{ $pending['code'] }}</strong></p>
        </div>

        <x-card class="mt-6">
            <form method="post" action="{{ route('payments.verify', [$mission, $application]) }}" class="space-y-5" novalidate>
                @csrf
                <x-field label="Code de confirmation" name="code">
                    <x-input name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="4" placeholder="4 chiffres" required autofocus />
                </x-field>
                <x-button type="submit" class="w-full">Sécuriser {{ App\Support\Money::fcfa($application->proposed_fcfa) }} FCFA</x-button>
                <p class="text-center text-sm text-ink-muted">{{ $application->user->name }} est prévenu dès que les fonds sont sécurisés.</p>
            </form>
        </x-card>
    </div>
</x-layouts.app>
