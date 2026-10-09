<x-layouts.app title="Connexion">
    <x-auth-card title="Bon retour parmi nous" subtitle="Connectez-vous pour retrouver vos missions et vos échanges.">
        <form method="post" action="{{ route('login') }}" class="space-y-5" novalidate>
            @csrf

            <x-field label="Adresse e-mail" name="email">
                <x-input name="email" type="email" autocomplete="email" required autofocus />
            </x-field>

            <x-field label="Mot de passe" name="password">
                <x-input name="password" type="password" autocomplete="current-password" required />
            </x-field>

            <div class="flex items-center justify-between gap-4">
                <label class="inline-flex min-h-11 items-center gap-2">
                    <input type="checkbox" name="remember" class="size-5 rounded-stamp border-2 border-ink accent-primary">
                    <span>Se souvenir de moi</span>
                </label>
                <a href="{{ route('password.request') }}" class="font-bold text-primary-strong underline decoration-2 underline-offset-4 hover:decoration-primary">Mot de passe oublié ?</a>
            </div>

            <x-button type="submit" class="w-full">Se connecter</x-button>
        </form>

        <x-slot:footer>
            Pas encore de compte ?
            <a href="{{ route('register') }}" class="font-bold text-primary-strong underline decoration-2 underline-offset-4 hover:decoration-primary">Rejoindre DevMarché</a>
        </x-slot:footer>
    </x-auth-card>
</x-layouts.app>
