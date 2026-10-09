@props(['title' => null, 'search' => true])

@php
    $navigation = [
        ['label' => 'Missions', 'route' => 'missions.index', 'icon' => 'briefcase', 'active' => 'missions.*'],
        ['label' => 'Q&R', 'route' => 'questions.index', 'icon' => 'message', 'active' => 'questions.*'],
        ['label' => 'Devs', 'route' => 'devs.index', 'icon' => 'users', 'active' => 'devs.index'],
    ];
    $me = auth()->user()?->loadMissing('profile');
    $meUrl = $me ? route('devs.show', $me) : route('login');
    $meActive = $me ? request()->routeIs('devs.show', 'profile.edit') && request()->route('user')?->is($me) : request()->routeIs('login', 'register');
    $publishUrl = Route::has('missions.create') ? route('missions.create') : null;
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ' : '' }}DevMarché</title>
    <meta name="description" content="La plateforme d'échange par et pour les développeurs d'Afrique francophone.">
    <meta name="theme-color" content="#314A82">

    {{-- Icônes (générées par php artisan brand:export puis scripts/brand-png.sh) --}}
    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">

    {{-- Partage (WhatsApp, réseaux) : valeurs par défaut, remplaçables par page --}}
    <meta property="og:site_name" content="DevMarché">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ? $title.' · ' : '' }}DevMarché">
    <meta property="og:description" content="{{ $description ?? 'La plateforme d\'échange par et pour les développeurs d\'Afrique francophone.' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/og-default.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="DevMarché, par et pour les devs d'Afrique francophone">
    <meta name="twitter:card" content="summary_large_image">

    {{-- Anti-flash : applique le thème avant le premier rendu. --}}
    <script>
        (function () {
            var theme = null;
            try { theme = localStorage.getItem('theme'); } catch (e) {}
            if (theme === 'dark' || (theme === null && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <link rel="preload" href="/fonts/newsreader-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/schibsted-grotesk-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    {{ $head ?? '' }}
</head>
<body class="min-h-dvh pb-safe md:pb-0">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-btn focus:bg-surface focus:px-4 focus:py-2 focus:shadow-card">
        Aller au contenu
    </a>

    {{-- Navigation flottante --}}
    <div class="sticky top-0 z-40 px-3 pt-3 sm:px-6">
        <header class="nav-glass mx-auto flex h-14 max-w-page items-center gap-4 rounded-nav px-3 sm:h-16 sm:px-4">
            <a href="{{ route('home') }}" class="shrink-0" aria-label="DevMarché, accueil">
                <x-logo />
            </a>

            <nav class="mx-auto hidden items-center gap-1 md:flex" aria-label="Navigation principale">
                @foreach ($navigation as $item)
                    @php($active = request()->routeIs($item['active']))
                    <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                       @class([
                           'rounded-btn px-3 py-2 font-medium transition',
                           'text-ink' => $active,
                           'text-ink-muted hover:text-ink' => ! $active,
                       ])>
                        {{ $item['label'] }}
                        @if ($active)
                            <span class="mx-auto mt-0.5 block h-0.5 w-5 rounded-full bg-secondary-400" aria-hidden="true"></span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-2 md:ml-0">
                @if ($search)
                    <form action="{{ route('devs.index') }}" method="get" role="search" class="hidden xl:block">
                        <label for="recherche-globale" class="sr-only">Rechercher un dev</label>
                        <div class="relative">
                            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-ink-muted" />
                            <input id="recherche-globale" type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher un dev…"
                                   class="h-10 w-52 rounded-btn border border-line bg-surface pl-9 pr-3 text-sm text-ink placeholder:text-ink-muted focus:border-primary-500 focus:outline-none focus:ring-3 focus:ring-primary-200 dark:focus:ring-primary-800">
                        </div>
                    </form>
                @endif

                <x-theme-toggle />

                @auth
                    <div class="relative hidden md:block" x-data="{ open: false }" x-on:keydown.escape="open = false" x-on:click.outside="open = false">
                        <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()"
                                class="flex items-center gap-2 rounded-btn py-1 pl-1 pr-2.5 transition hover:bg-surface">
                            <x-avatar :user="$me" size="xs" />
                            <x-reputation :points="$me->reputation" />
                            <span class="sr-only">Ouvrir le menu du compte</span>
                        </button>
                        <div x-cloak x-show="open" x-transition.opacity
                             class="absolute right-0 mt-2 w-60 overflow-hidden rounded-2xl border border-line bg-surface p-1.5 shadow-card-hover">
                            <p class="truncate px-3 py-2 text-sm text-ink-muted">{{ $me->name }}</p>
                            <a href="{{ route('devs.show', $me) }}" class="flex items-center gap-2 rounded-btn px-3 py-2 font-medium text-ink hover:bg-bg-alt">
                                <x-icon name="user" class="size-4" /> Mon profil
                            </a>
                            <a href="{{ route('profile.edit', $me) }}" class="flex items-center gap-2 rounded-btn px-3 py-2 font-medium text-ink hover:bg-bg-alt">
                                <x-icon name="pencil" class="size-4" /> Modifier mon profil
                            </a>
                            <form method="post" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 rounded-btn px-3 py-2 text-left font-medium text-ink hover:bg-bg-alt">
                                    <x-icon name="log-out" class="size-4" /> Se déconnecter
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="hidden md:block">
                        <x-button :href="route('login')" variant="secondary" size="sm">Connexion</x-button>
                    </div>
                @endauth

                @if ($publishUrl)
                    <div class="hidden md:block">
                        <x-button :href="$publishUrl" variant="accent" size="sm">Publier une mission</x-button>
                    </div>
                @else
                    @guest
                        <div class="hidden md:block">
                            <x-button :href="route('register')" variant="accent" size="sm">Créer un compte</x-button>
                        </div>
                    @endguest
                @endif
            </div>
        </header>
    </div>

    <main id="contenu" class="mx-auto max-w-page px-4 pb-16 pt-10 sm:px-6 sm:pt-16">
        @if (session('status'))
            <div role="status" class="mb-8 flex items-center gap-3 rounded-2xl border border-line bg-surface px-4 py-3 font-medium text-ink shadow-card">
                <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-primary-600 text-white dark:bg-secondary-400 dark:text-primary-950">
                    <x-icon name="check" class="size-4" />
                </span>
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    {{-- Pied de page : grand texte qui défile, puis les liens --}}
    <footer class="overflow-hidden border-t border-line bg-bg-alt">
        <div class="py-10 sm:py-14" aria-hidden="true">
            <div class="defile flex w-max gap-12 whitespace-nowrap font-serif text-5xl font-medium tracking-tight text-ink/10 sm:text-7xl dark:text-ink/15">
                @foreach (range(1, 2) as $copy)
                    <span>Du travail, de l'aide, des pairs.</span>
                    <span class="italic text-primary-600/25 dark:text-secondary-300/30">Par et pour les devs d'Afrique.</span>
                @endforeach
            </div>
        </div>
        <div class="mx-auto grid max-w-page gap-8 border-t border-line px-4 py-10 sm:grid-cols-[2fr_1fr_1fr] sm:px-6">
            <div class="space-y-3">
                <x-logo />
                <p class="max-w-sm text-sm text-ink-muted">La plateforme d'échange par et pour les développeurs d'Afrique francophone.</p>
            </div>
            <nav aria-label="Plateforme" class="space-y-2 text-sm">
                <p class="font-semibold text-ink">Plateforme</p>
                @foreach ($navigation as $item)
                    <a href="{{ route($item['route']) }}" class="block text-ink-muted hover:text-ink">{{ $item['label'] }}</a>
                @endforeach
            </nav>
            <div class="space-y-2 text-sm">
                <p class="font-semibold text-ink">Le projet</p>
                <p class="text-ink-muted">Équipe Baobab Stack</p>
                <p class="text-ink-muted">Concours CADEV 2026</p>
            </div>
        </div>
    </footer>

    {{-- Barre du bas (mobile) : flottante, même matière que la navigation --}}
    <nav class="fixed inset-x-3 bottom-3 z-40 md:hidden" aria-label="Navigation principale" style="margin-bottom: env(safe-area-inset-bottom)">
        <ul class="nav-glass grid grid-cols-4 rounded-2xl p-1.5 shadow-card-hover">
            @foreach ($navigation as $item)
                @php($active = request()->routeIs($item['active']))
                <li>
                    <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                       @class([
                           'flex h-14 flex-col items-center justify-center gap-0.5 rounded-xl text-xs font-medium transition',
                           'bg-surface text-primary-600 shadow-soft ring-1 ring-secondary-400 dark:text-secondary-400' => $active,
                           'text-ink-muted' => ! $active,
                       ])>
                        <x-icon :name="$item['icon']" class="size-5" />
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
            <li>
                <a href="{{ $meUrl }}" @if ($meActive) aria-current="page" @endif
                   @class([
                       'flex h-14 flex-col items-center justify-center gap-0.5 rounded-xl text-xs font-medium transition',
                       'bg-surface text-primary-600 shadow-soft ring-1 ring-secondary-400 dark:text-secondary-400' => $meActive,
                       'text-ink-muted' => ! $meActive,
                   ])>
                    <x-icon name="user" class="size-5" />
                    Moi
                </a>
            </li>
        </ul>

        @if ($publishUrl)
            <a href="{{ $publishUrl }}" class="btn-accent absolute -top-16 right-1 inline-flex size-13 items-center justify-center rounded-2xl" aria-label="Publier une mission">
                <x-icon name="plus" class="size-6" />
            </a>
        @endif
    </nav>

    @livewireScripts
</body>
</html>
