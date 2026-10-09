<x-layouts.app title="Mot de passe oublié">
    <x-auth-card title="Mot de passe oublié" subtitle="Indiquez votre adresse e-mail : nous vous envoyons un lien pour en choisir un nouveau.">
        <form method="post" action="{{ route('password.email') }}" class="space-y-5" novalidate>
            @csrf

            <x-field label="Adresse e-mail" name="email">
                <x-input name="email" type="email" autocomplete="email" required autofocus />
            </x-field>

            <x-button type="submit" class="w-full">Envoyer le lien</x-button>
        </form>

        <x-slot:footer>
            <a href="{{ route('login') }}" class="font-bold text-primary-strong underline decoration-2 underline-offset-4 hover:decoration-primary">Retour à la connexion</a>
        </x-slot:footer>
    </x-auth-card>
</x-layouts.app>
