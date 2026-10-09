@props(['title' => null, 'search' => true])

@php
    $navigation = [
        ['label' => 'Missions', 'route' => 'missions.index', 'icon' => 'briefcase', 'active' => 'missions.*'],
        ['label' => 'Q&R', 'route' => 'questions.index', 'icon' => 'message', 'active' => 'questions.*'],
        ['label' => 'Devs', 'route' => 'devs.index', 'icon' => 'users', 'active' => 'devs.index'],
    ];
    $me = auth()->user()?->loadMissing("profile");
    $meUrl = $me ? route('devs.show', $me) : route('login');
    $meActive = $me ? request()->routeIs('devs.show', 'profile.edit') && request()->route('user')?->is($me) : request()->routeIs('login', 'register');
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ' : '' }}DevMarché</title>
    <meta name="description" content="La plateforme d'échange par et pour les développeurs d'Afrique francophone.">
    <meta name="theme-color" content="#FAF6EF" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#13152B" media="(prefers-color-scheme: dark)">

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

    <link rel="preload" href="/fonts/danfo-latin-elsh.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/jetbrains-mono-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    {{ $head ?? '' }}
</head>
<body class="min-h-dvh pb-safe md:pb-0">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:border-2 focus:border-ink focus:bg-surface focus:px-4 focus:py-2">
        Aller au contenu
    </a>

    {{-- Barre du haut, puis la bande peinte de la carrosserie --}}
    <header class="sticky top-0 z-30 bg-bg">
        <div class="mx-auto flex h-16 max-w-page items-center gap-6 px-4 sm:px-6">
            <a href="{{ route('home') }}" class="shrink-0" aria-label="DevMarché, accueil">
                <x-logo class="h-7 w-auto sm:h-8" />
            </a>

            <nav class="hidden h-full items-stretch gap-5 md:flex" aria-label="Navigation principale">
                @foreach ($navigation as $item)
                    @php($active = request()->routeIs($item['active']))
                    <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                       @class([
                           'flex items-center border-b-4 pt-1 font-bold transition-colors',
                           'border-primary text-ink' => $active,
                           'border-transparent text-ink-muted hover:border-ink hover:text-ink' => ! $active,
                       ])>{{ $item['label'] }}</a>
                @endforeach
            </nav>

            @if ($search)
                <form action="{{ route('devs.index') }}" method="get" role="search" class="ml-auto hidden w-full max-w-60 lg:block">
                    <label for="recherche-globale" class="sr-only">Rechercher un dev</label>
                    <div class="relative">
                        <x-icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-ink-muted" />
                        <input id="recherche-globale" type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher un dev…"
                               class="h-10 w-full rounded-field border-2 border-ink bg-surface pl-8 pr-3 text-sm text-ink placeholder:text-ink-muted focus:shadow-hard-primary focus:outline-none">
                    </div>
                </form>
            @endif

            <div @class(['flex items-center gap-2', 'ml-auto' => true, 'lg:ml-0' => $search])>
                <x-theme-toggle />

                @auth
                    <div class="relative hidden md:block" x-data="{ open: false }" x-on:keydown.escape="open = false" x-on:click.outside="open = false">
                        <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()"
                                class="flex items-center gap-2 rounded-btn border-2 border-transparent p-1 pr-2 hover:border-ink">
                            <x-avatar :user="$me" size="sm" />
                            <x-reputation :points="$me->reputation" />
                            <span class="sr-only">Ouvrir le menu du compte</span>
                        </button>
                        <div x-cloak x-show="open"
                             class="absolute right-0 mt-2 w-60 rounded-block border-2 border-ink bg-surface shadow-hard">
                            <p class="etiquette truncate border-b-2 border-ink px-3 py-2 text-ink-muted">{{ $me->name }}</p>
                            <a href="{{ route('devs.show', $me) }}" class="flex items-center gap-2 px-3 py-2.5 font-medium hover:bg-accent/25">
                                <x-icon name="user" class="size-4" /> Mon profil
                            </a>
                            <a href="{{ route('profile.edit', $me) }}" class="flex items-center gap-2 px-3 py-2.5 font-medium hover:bg-accent/25">
                                <x-icon name="pencil" class="size-4" /> Modifier mon profil
                            </a>
                            <form method="post" action="{{ route('logout') }}" class="border-t border-ink">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 px-3 py-2.5 text-left font-medium hover:bg-accent/25">
                                    <x-icon name="log-out" class="size-4" /> Se déconnecter
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="hidden px-2 font-bold text-ink underline decoration-2 underline-offset-4 hover:decoration-primary md:inline">Connexion</a>
                    <div class="hidden md:block">
                        <x-button :href="route('register')" size="sm">Créer un compte</x-button>
                    </div>
                @endauth
            </div>
        </div>
        <div class="bande-peinte" aria-hidden="true"></div>
    </header>

    <main id="contenu" class="mx-auto max-w-page px-4 py-6 sm:px-6 sm:py-10">
        @if (session('status'))
            <div role="status" class="mb-6 flex items-center gap-2 rounded-block border-2 border-success bg-surface px-4 py-3 font-bold text-success">
                <x-icon name="check" class="size-5 shrink-0" />
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="filet mt-12">
        <div class="mx-auto flex max-w-page flex-col gap-3 px-4 pb-10 pt-8 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                <x-logo class="h-6 w-auto" />
                <span class="text-ink-muted">Par et pour les devs d'Afrique francophone.</span>
            </div>
            <p class="etiquette text-ink-muted">Équipe Baobab Stack · CADEV 2026</p>
        </div>
    </footer>

    {{-- Barre de navigation du bas (mobile) : aplat indigo, onglet actif marqué en or --}}
    <nav class="fixed inset-x-0 bottom-0 z-30 border-t-2 border-ink bg-secondary pb-[env(safe-area-inset-bottom)] text-bg md:hidden" aria-label="Navigation principale">
        <ul class="relative grid grid-cols-4">
            @foreach ($navigation as $item)
                @php($active = request()->routeIs($item['active']))
                <li>
                    <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                       @class([
                           'flex h-16 flex-col items-center justify-center gap-1 border-t-[3px] text-sm font-bold',
                           'border-accent' => $active,
                           'border-transparent opacity-80' => ! $active,
                       ])>
                        <x-icon :name="$item['icon']" class="size-6" />
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
            <li>
                <a href="{{ $meUrl }}" @if ($meActive) aria-current="page" @endif
                   @class([
                       'flex h-16 flex-col items-center justify-center gap-1 border-t-[3px] text-sm font-bold',
                       'border-accent' => $meActive,
                       'border-transparent opacity-80' => ! $meActive,
                   ])>
                    <x-icon name="user" class="size-6" />
                    Moi
                </a>
            </li>
        </ul>

        @if (Route::has('missions.create'))
            <a href="{{ route('missions.create') }}"
               class="absolute -top-8 right-4 inline-flex size-14 items-center justify-center rounded-btn border-2 border-ink bg-primary text-on-primary shadow-hard"
               aria-label="Publier une mission">
                <x-icon name="plus" class="size-7" />
            </a>
        @endif
    </nav>

    @livewireScripts
</body>
</html>
