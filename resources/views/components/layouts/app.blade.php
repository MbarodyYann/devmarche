@props(['title' => null])

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

    <link rel="preload" href="/fonts/bricolage-grotesque-latin-800-normal.woff2" as="font" type="font/woff2" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    {{ $head ?? '' }}
</head>
<body class="min-h-dvh pb-safe md:pb-0">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-btn focus:bg-surface focus:px-4 focus:py-2">
        Aller au contenu
    </a>

    {{-- Barre du haut --}}
    <header class="sticky top-0 z-30 border-b border-border bg-bg/95 backdrop-blur supports-[backdrop-filter]:bg-bg/85">
        <div class="mx-auto flex h-16 max-w-page items-center gap-4 px-4 sm:px-6">
            <a href="{{ route('home') }}" class="shrink-0" aria-label="DevMarché, accueil">
                <x-logo class="h-6 w-auto sm:h-7" />
            </a>

            <nav class="ml-4 hidden items-center gap-1 md:flex" aria-label="Navigation principale">
                @foreach ($navigation as $item)
                    <a href="{{ route($item['route']) }}"
                       @class([
                           'rounded-btn px-3 py-2 font-semibold transition-colors',
                           'bg-secondary-soft text-secondary' => request()->routeIs($item['active']),
                           'text-ink-muted hover:text-ink' => ! request()->routeIs($item['active']),
                       ])
                       @if (request()->routeIs($item['active'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <form action="{{ route('devs.index') }}" method="get" role="search" class="ml-auto hidden w-full max-w-xs lg:block">
                <label for="recherche-globale" class="sr-only">Rechercher un dev</label>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-ink-muted" />
                    <input id="recherche-globale" type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher un dev…"
                           class="h-10 w-full rounded-full border border-border bg-surface pl-9 pr-4 text-sm text-ink placeholder:text-ink-muted focus:border-secondary focus:outline-none focus:ring-2 focus:ring-secondary/25">
                </div>
            </form>

            <div class="ml-auto flex items-center gap-1 lg:ml-0">
                <x-theme-toggle />

                @auth
                    <div class="relative hidden md:block" x-data="{ open: false }" x-on:keydown.escape="open = false" x-on:click.outside="open = false">
                        <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()"
                                class="flex items-center gap-2 rounded-full p-1 pr-3 hover:bg-secondary-soft">
                            <x-avatar :user="$me" size="sm" />
                            <x-reputation :points="$me->reputation" />
                            <span class="sr-only">Ouvrir le menu du compte</span>
                        </button>
                        <div x-cloak x-show="open" x-transition.opacity
                             class="absolute right-0 mt-2 w-56 rounded-card border border-border bg-surface p-1.5 shadow-card">
                            <p class="truncate px-3 py-2 text-sm text-ink-muted">{{ $me->name }}</p>
                            <a href="{{ route('devs.show', $me) }}" class="flex items-center gap-2 rounded-btn px-3 py-2 hover:bg-secondary-soft">
                                <x-icon name="user" class="size-4" /> Mon profil
                            </a>
                            <a href="{{ route('profile.edit', $me) }}" class="flex items-center gap-2 rounded-btn px-3 py-2 hover:bg-secondary-soft">
                                <x-icon name="pencil" class="size-4" /> Modifier mon profil
                            </a>
                            <form method="post" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 rounded-btn px-3 py-2 text-left hover:bg-secondary-soft">
                                    <x-icon name="log-out" class="size-4" /> Se déconnecter
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="hidden rounded-btn px-3 py-2 font-semibold text-ink-muted hover:text-ink md:inline-flex">Connexion</a>
                    <div class="hidden md:block">
                        <x-button :href="route('register')" size="sm">Créer un compte</x-button>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <main id="contenu" class="mx-auto max-w-page px-4 py-6 sm:px-6 sm:py-10">
        @if (session('status'))
            <div role="status" class="mb-6 flex items-center gap-2 rounded-card border border-success/30 bg-success/10 px-4 py-3 font-medium text-success">
                <x-icon name="check" class="size-5 shrink-0" />
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="relative mt-10 overflow-hidden border-t border-border">
        <div class="frise absolute inset-x-0 top-0 h-12" aria-hidden="true"></div>
        <div class="relative mx-auto flex max-w-page flex-col gap-2 px-4 pb-8 pt-16 text-sm text-ink-muted sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex items-center gap-3">
                <x-logo class="h-5 w-auto" />
                <span>Par et pour les devs d'Afrique francophone.</span>
            </div>
            <p>Projet de l'équipe Baobab Stack · CADEV 2026</p>
        </div>
    </footer>

    {{-- Barre de navigation du bas (mobile) --}}
    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-border bg-surface pb-[env(safe-area-inset-bottom)] md:hidden" aria-label="Navigation principale">
        <ul class="relative grid grid-cols-4">
            @foreach ($navigation as $item)
                @php($active = request()->routeIs($item['active']))
                <li>
                    <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                       @class([
                           'flex h-16 flex-col items-center justify-center gap-1 text-sm font-medium',
                           'text-primary-strong' => $active,
                           'text-ink-muted' => ! $active,
                       ])>
                        <x-icon :name="$item['icon']" class="size-6" />
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
            <li>
                <a href="{{ $meUrl }}" @if ($meActive) aria-current="page" @endif
                   @class([
                       'flex h-16 flex-col items-center justify-center gap-1 text-sm font-medium',
                       'text-primary-strong' => $meActive,
                       'text-ink-muted' => ! $meActive,
                   ])>
                    <x-icon name="user" class="size-6" />
                    Moi
                </a>
            </li>
        </ul>

        @if (Route::has('missions.create'))
            <a href="{{ route('missions.create') }}"
               class="absolute -top-7 right-4 inline-flex size-14 items-center justify-center rounded-full bg-primary text-on-primary shadow-lg ring-4 ring-bg"
               aria-label="Publier une mission">
                <x-icon name="plus" class="size-7" />
            </a>
        @endif
    </nav>

    @livewireScripts
</body>
</html>
