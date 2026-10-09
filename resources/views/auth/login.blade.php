<x-layouts.app title="Connexion" description="Connectez-vous à DevMarché pour postuler aux missions, répondre aux questions et suivre vos cauris.">
    <x-auth-card :demo="true" title="Content de vous revoir.">
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
                    <input type="checkbox" name="remember" class="size-5 rounded border-line accent-primary-600">
                    <span>Se souvenir de moi</span>
                </label>
                <a href="{{ route('password.request') }}" class="font-semibold text-primary-600 underline-offset-4 hover:underline dark:text-primary-300">Mot de passe oublié ?</a>
            </div>

            <x-button type="submit" class="w-full">Se connecter</x-button>
        </form>

        <x-slot:footer>
            <a href="{{ route('register') }}" class="text-sm text-ink-muted underline-offset-4 hover:text-ink hover:underline">Pas encore membre ? {{ App\Support\Copy::SIGNUP_CTA }}</a>
        </x-slot:footer>
    </x-auth-card>
</x-layouts.app>
