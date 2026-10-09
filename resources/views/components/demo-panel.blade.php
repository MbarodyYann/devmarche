{{-- Démo en un clic (02, « Spécial jury ») : connexion en un clic, seulement si APP_DEMO=true. --}}
@if (App\Support\Demo::enabled())
    <section aria-labelledby="demo-titre" data-demo-panel {{ $attributes->class('rounded-card border border-secondary-200 bg-secondary-50 p-5 text-primary-900 dark:border-secondary-900 dark:bg-secondary-950 dark:text-white') }}>
        <h2 id="demo-titre" class="font-serif text-xl font-medium">Essayer la démo</h2>
        <p class="mt-1 text-sm text-primary-800 dark:text-white/75">Testez DevMarché sans créer de compte, avec des données réalistes.</p>
        <div class="mt-4 grid gap-2">
            @foreach (App\Support\Demo::PERSONAS as $key => $persona)
                <form method="post" action="{{ route('demo.login', $key) }}">
                    @csrf
                    <x-button type="submit" :variant="$loop->first ? 'primary' : 'secondary'" class="w-full">{{ $persona['label'] }}</x-button>
                </form>
            @endforeach
        </div>
    </section>
@endif
