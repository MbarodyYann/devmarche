<x-layouts.app title="Inscription">
    <x-auth-card eyebrow="Inscription" title="Votre profil en ligne en 2 minutes." subtitle="Nom, e-mail, pays et ville : c'est tout. Vous ajouterez vos compétences ensuite.">
        <form method="post" action="{{ route('register') }}" class="space-y-5" novalidate>
            @csrf

            <x-field label="Nom complet" name="name">
                <x-input name="name" autocomplete="name" required autofocus placeholder="Ex. Aïssatou Diallo" />
            </x-field>

            <x-field label="Adresse e-mail" name="email">
                <x-input name="email" type="email" autocomplete="email" required />
            </x-field>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-field label="Pays" name="country">
                    <x-select name="country" :options="$countries" value="SN" required />
                </x-field>
                <x-field label="Ville" name="city">
                    <x-input name="city" autocomplete="address-level2" required placeholder="Ex. Dakar" />
                </x-field>
            </div>

            <x-field label="Mot de passe" name="password" hint="8 caractères minimum.">
                <x-input name="password" type="password" autocomplete="new-password" required />
            </x-field>

            <x-field label="Confirmer le mot de passe" name="password_confirmation">
                <x-input name="password_confirmation" type="password" autocomplete="new-password" required />
            </x-field>

            <x-button type="submit" size="lg" class="w-full">{{ App\Support\Copy::SIGNUP_CTA }}</x-button>
            <x-reassurance :items="[App\Support\Copy::REASSURANCE_RISK]" class="justify-center" />
        </form>

        <x-slot:footer>
            <a href="{{ route('login') }}" class="text-sm text-ink-muted underline-offset-4 hover:text-ink hover:underline">Déjà membre ? Se connecter</a>
        </x-slot:footer>
    </x-auth-card>
</x-layouts.app>
