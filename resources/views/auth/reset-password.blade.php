<x-layouts.app title="Nouveau mot de passe">
    <x-auth-card eyebrow="Presque fini" title="Choisir un nouveau mot de passe">
        <form method="post" action="{{ route('password.update') }}" class="space-y-5" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <x-field label="Adresse e-mail" name="email">
                <x-input name="email" type="email" :value="$request->email" autocomplete="email" required />
            </x-field>

            <x-field label="Nouveau mot de passe" name="password" hint="8 caractères minimum.">
                <x-input name="password" type="password" autocomplete="new-password" required autofocus />
            </x-field>

            <x-field label="Confirmer le mot de passe" name="password_confirmation">
                <x-input name="password_confirmation" type="password" autocomplete="new-password" required />
            </x-field>

            <x-button type="submit" class="w-full">Enregistrer le mot de passe</x-button>
        </form>
    </x-auth-card>
</x-layouts.app>
