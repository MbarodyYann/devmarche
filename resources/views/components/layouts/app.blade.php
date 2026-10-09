@props(['title' => null, 'description' => null, 'ogImage' => null, 'pageHero' => null, 'canonical' => null, 'noindex' => false])

@php
    $navigation = [
        ['label' => 'Missions', 'route' => 'missions.index', 'icon' => 'briefcase', 'active' => 'missions.*'],
        ['label' => 'Q&R', 'route' => 'questions.index', 'icon' => 'message', 'active' => 'questions.*'],
        ['label' => 'Devs', 'route' => 'devs.index', 'icon' => 'users', 'active' => 'devs.index'],
        ['label' => 'Le Pouls', 'route' => 'pulse', 'icon' => 'chart', 'active' => 'pulse'],
    ];
    $me = auth()->user()?->loadMissing('profile');
    $meUrl = $me ? route('devs.show', $me) : route('login');
    $meActive = $me ? request()->routeIs('devs.show', 'profile.edit') && request()->route('user')?->is($me) : request()->routeIs('login', 'register');
    $publishUrl = Route::has('missions.create') ? route('missions.create') : null;
    // Notifications du membre : compteur des non lues et les 10 dernières (cloche et menu « Moi »).
    $unreadCount = $me ? $me->unreadNotifications()->count() : 0;
    $latestNotifications = $me ? $me->notifications()->latest()->limit(10)->get() : collect();
    // Bandeau indigo sous la navigation : hero de l'accueil (slot) ou en-tête compact d'une page de liste.
    $hasHero = isset($hero) || $pageHero;
    // Une action de l'en-tête compact déjà proposée par la navigation n'y est pas répétée
    // (« Publier une mission » pour un membre) ; « Poser ma question » reste.
    $heroAction = $pageHero['action'] ?? null;
    if ($heroAction && $me && $heroAction['route'] === 'missions.create') {
        $heroAction = null;
    }
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        // Sans titre de page (accueil) : le nom suivi de la promesse.
        $fullTitle = $title ? $title.' · DevMarché' : 'DevMarché · Missions en FCFA pour les devs d\'Afrique francophone';
        // Description : celle de la page, sinon le sous-titre de l'en-tête compact, sinon celle du site.
        $metaDescription = App\Support\Seo::excerpt($description ?? $pageHero['subtitle'] ?? App\Support\Seo::DEFAULT_DESCRIPTION);
        $canonicalUrl = $canonical ?? url()->current();
        $metaImage = $ogImage ?? asset('images/og-default.png');
    @endphp
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @if ($noindex)
        <meta name="robots" content="noindex">
    @endif
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
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="DevMarché, par et pour les devs d'Afrique francophone">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $metaImage }}">

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
<body class="min-h-dvh pb-safe lg:pb-0">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-btn focus:bg-surface focus:px-4 focus:py-2 focus:shadow-card">
        Aller au contenu
    </a>

    {{-- Bandeau discret du mode démo (comptes de démonstration, APP_DEMO=true) --}}
    @if ($demoPersona = App\Support\Demo::personaName(auth()->user()))
        <p role="status" data-demo-banner class="bg-primary-950 px-4 py-1.5 text-center text-[13px] text-white/80">
            Vous êtes en mode démo ({{ $demoPersona }}). Les données sont réinitialisées chaque nuit.
        </p>
    @endif

    {{-- Navigation flottante. Sur une page avec bandeau hero, elle passe en version claire tant qu'on est sur le bandeau. --}}
    {{-- Sous 1 024 px, dès le premier défilement, la barre se colle au bord haut (nav-stuck) : plus d'espace
         au-dessus où le contenu défilerait. La hauteur totale ne change pas, donc rien ne saute. --}}
    <div class="sticky top-0 z-40 px-2 pt-3 sm:px-6"
         x-data="{ onHero: {{ $hasHero ? 'true' : 'false' }}, scrolled: false }"
         x-init="const hero = document.getElementById('hero'); const update = () => { scrolled = window.scrollY > 0; onHero = !! hero && window.scrollY < hero.offsetHeight - 80 }; update(); window.addEventListener('scroll', update, { passive: true })"
         x-bind:class="{ 'nav-stuck': scrolled }">
        <header @class(['mx-auto flex h-14 max-w-page items-center gap-2 rounded-nav px-2 sm:h-16 lg:transition-colors sm:px-4 lg:gap-4', 'nav-glass' => ! $hasHero, 'nav-on-hero' => $hasHero])
                @if ($hasHero) x-bind:class="{ 'is-glass': scrolled && onHero, 'is-scrolled': ! onHero }" @endif>
            <a href="{{ route('home') }}" class="shrink-0" aria-label="DevMarché, accueil">
                <x-logo :compact="auth()->guest()" />
            </a>

            <nav class="mx-auto hidden items-center gap-1 lg:flex" aria-label="Navigation principale">
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

            <div class="ml-auto flex items-center gap-2 lg:ml-0">

                {{-- Sous 1 024 px, la bascule de thème est dans le pied de page (place pour le nom et le CTA). --}}
                <x-theme-toggle class="max-lg:hidden" />

                @auth
                    {{-- Cloche : compteur ambre des non lues, 10 dernières, « Tout marquer comme lu ». --}}
                    <div class="relative hidden lg:block" x-data="{ open: false }" x-on:keydown.escape="open = false" x-on:click.outside="open = false" data-bell>
                        <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()"
                                aria-label="Notifications{{ $unreadCount ? ', '.$unreadCount.' non lue'.($unreadCount > 1 ? 's' : '') : '' }}"
                                class="relative inline-flex size-10 items-center justify-center rounded-btn text-ink-soft transition hover:bg-surface hover:text-ink">
                            <x-icon name="bell" class="size-5" />
                            @if ($unreadCount)
                                <span class="absolute -right-0.5 -top-0.5 inline-flex min-w-5 items-center justify-center rounded-full bg-secondary-400 px-1 text-xs font-semibold text-primary-950" aria-hidden="true">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                            @endif
                        </button>
                        <div x-cloak x-show="open" x-transition.opacity
                             class="nav-menu absolute right-0 mt-2 w-96 overflow-hidden rounded-2xl border border-line bg-surface shadow-card-hover">
                            <div class="flex items-center justify-between gap-3 border-b border-line px-4 py-3">
                                <p class="font-semibold text-ink">Notifications</p>
                                @if ($unreadCount)
                                    <form method="post" action="{{ route('notifications.read-all') }}">
                                        @csrf
                                        <button type="submit" class="text-sm font-semibold text-primary-600 underline-offset-4 hover:underline">Tout marquer comme lu</button>
                                    </form>
                                @endif
                            </div>
                            @if ($latestNotifications->isEmpty())
                                <p class="px-4 py-6 text-sm text-ink-muted">Rien de neuf pour l'instant.</p>
                            @else
                                <ul class="max-h-96 overflow-y-auto p-1.5">
                                    @foreach ($latestNotifications as $notification)
                                        <li><x-notification-item :notification="$notification" /></li>
                                    @endforeach
                                </ul>
                            @endif
                            <a href="{{ route('notifications.index') }}" class="block border-t border-line px-4 py-3 text-center text-sm font-semibold text-primary-600 hover:bg-bg-alt">Voir toutes les notifications</a>
                        </div>
                    </div>

                    <div class="relative hidden lg:block" x-data="{ open: false }" x-on:keydown.escape="open = false" x-on:click.outside="open = false">
                        <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()"
                                class="flex items-center gap-2 rounded-btn py-1 pl-1 pr-2.5 transition hover:bg-surface">
                            <x-avatar :user="$me" size="xs" />
                            <x-reputation :points="$me->reputation" />
                            <span class="sr-only">Ouvrir le menu du compte</span>
                        </button>
                        <div x-cloak x-show="open" x-transition.opacity
                             class="nav-menu absolute right-0 mt-2 w-60 overflow-hidden rounded-2xl border border-line bg-surface p-1.5 shadow-card-hover">
                            <p class="truncate px-3 py-2 text-sm text-ink-muted">{{ $me->name }}</p>
                            @if ($me->profile)
                                <a href="{{ route('devs.show', $me) }}" class="flex items-center gap-2 rounded-btn px-3 py-2 font-medium text-ink hover:bg-bg-alt">
                                    <x-icon name="user" class="size-4" /> Mon profil
                                </a>
                                <a href="{{ route('profile.edit', $me) }}" class="flex items-center gap-2 rounded-btn px-3 py-2 font-medium text-ink hover:bg-bg-alt">
                                    <x-icon name="pencil" class="size-4" /> Modifier mon profil
                                </a>
                            @endif
                            @can('moderate')
                                <a href="{{ route('moderation.index') }}" class="flex items-center gap-2 rounded-btn px-3 py-2 font-medium text-ink hover:bg-bg-alt">
                                    <x-icon name="alert" class="size-4" /> Modération
                                </a>
                            @endcan
                            <form method="post" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 rounded-btn px-3 py-2 text-left font-medium text-ink hover:bg-bg-alt">
                                    <x-icon name="log-out" class="size-4" /> Se déconnecter
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="hidden lg:block">
                        <x-button :href="route('login')" variant="secondary" size="sm">Connexion</x-button>
                    </div>
                @endauth

                {{-- Visiteur : le CTA principal, mot pour mot, visible en permanence sur toutes les pages
                     (il coexiste avec celui du hero de l'accueil). Membre : l'accent « Publier ». --}}
                @guest
                    <div class="hidden lg:block" data-nav-cta>
                        <x-button :href="route('register')" size="sm">{{ App\Support\Copy::SIGNUP_CTA }}</x-button>
                    </div>
                    {{-- Sous 1 024 px (barre du bas affichée) : petit bouton ambre (36 px). « Connexion » reste dans l'onglet « Moi » de la barre du bas. --}}
                    <div class="lg:hidden" data-nav-cta-mobile>
                        <x-button :href="route('register')" variant="cta" size="xs">{{ App\Support\Copy::SIGNUP_CTA }}</x-button>
                    </div>
                @else
                    @if ($publishUrl)
                        <div class="hidden lg:block">
                            <x-button :href="$publishUrl" variant="accent" size="sm">Publier une mission</x-button>
                        </div>
                    @endif
                @endguest
            </div>
        </header>
    </div>

    {{-- Bandeau hero pleine largeur (accueil), glissé sous la navigation flottante --}}
    @isset($hero)
        {{ $hero }}
    @elseif ($pageHero)
        <x-list-hero :start="$pageHero['start']" :end="$pageHero['end']" :subtitle="$pageHero['subtitle']" :action="$heroAction" />
    @endisset

    <main id="contenu" @class(['mx-auto max-w-page px-4 pb-16 sm:px-6', 'pt-10 sm:pt-16' => ! $hasHero, 'pt-0' => $hasHero])>
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
        {{-- Texte défilant : couleurs pleines (contraste ≥ 4,5:1). Si l'utilisateur préfère réduire les
             animations, il est arrêté et affiché en entier (une seule copie, retour à la ligne). --}}
        <div class="py-10 sm:py-14">
            <p class="sr-only">Du travail, de l'aide, des pairs. Par et pour les devs d'Afrique.</p>
            <div class="defile flex w-max gap-12 whitespace-nowrap font-serif text-5xl font-medium tracking-tight text-ink sm:text-7xl motion-reduce:mx-auto motion-reduce:w-auto motion-reduce:max-w-page motion-reduce:flex-wrap motion-reduce:justify-center motion-reduce:gap-x-4 motion-reduce:gap-y-1 motion-reduce:px-4 motion-reduce:whitespace-normal motion-reduce:text-center" aria-hidden="true">
                @foreach (range(1, 2) as $copy)
                    <span @class(['motion-reduce:hidden' => $copy === 2])>Du travail, de l'aide, des pairs.</span>
                    <span @class(['italic text-primary-600 dark:text-secondary-400', 'motion-reduce:hidden' => $copy === 2])>Par et pour les devs d'Afrique.</span>
                @endforeach
            </div>
        </div>
        <div class="mx-auto grid max-w-page gap-8 border-t border-line px-4 py-10 sm:grid-cols-[2fr_1fr_1fr] sm:px-6">
            <div>
                <x-logo />
            </div>
            <nav aria-label="Plateforme" class="space-y-2 text-sm">
                <p class="font-semibold text-ink">Plateforme</p>
                @foreach ($navigation as $item)
                    <a href="{{ route($item['route']) }}" class="block text-ink-muted hover:text-ink">{{ $item['label'] }}</a>
                @endforeach
            </nav>
            <nav aria-label="Le projet" class="space-y-2 text-sm">
                <p class="font-semibold text-ink">Le projet</p>
                <a href="{{ route('about') }}" class="block text-ink-muted hover:text-ink">À propos</a>
            </nav>
        </div>
        <div class="mx-auto flex max-w-page items-center justify-between gap-4 border-t border-line px-4 py-4 text-sm text-ink-muted sm:px-6">
            <p>© 2026 DevMarché</p>
            <x-theme-toggle class="lg:hidden" />
        </div>
    </footer>

    {{-- Barre du bas (mobile) : flottante, même matière que la navigation --}}
    <nav class="fixed inset-x-3 bottom-3 z-40 lg:hidden" aria-label="Navigation principale" style="margin-bottom: env(safe-area-inset-bottom)">
        {{-- Opaque : le texte de la page ne doit pas se lire à travers. --}}
        <ul class="grid grid-cols-5 rounded-2xl border border-line bg-surface p-1.5 shadow-card-hover">
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
            <li @auth class="relative" x-data="{ me: false }" x-on:keydown.escape="me = false" x-on:click.outside="me = false" @endauth>
                @auth
                    {{-- Membre : « Moi » ouvre un menu (notifications, profil, déconnexion) ; pastille ambre si non lues. --}}
                    <button type="button" x-on:click="me = ! me" x-bind:aria-expanded="me.toString()" data-me-menu
                            aria-label="Moi{{ $unreadCount ? ', '.$unreadCount.' notification'.($unreadCount > 1 ? 's' : '').' non lue'.($unreadCount > 1 ? 's' : '') : '' }}"
                            @class([
                                'relative flex h-14 w-full flex-col items-center justify-center gap-0.5 rounded-xl text-xs font-medium transition',
                                'bg-surface text-primary-600 shadow-soft ring-1 ring-secondary-400 dark:text-secondary-400' => $meActive,
                                'text-ink-muted' => ! $meActive,
                            ])>
                        <x-icon name="user" class="size-5" />
                        Moi
                        @if ($unreadCount)
                            <span class="absolute right-3 top-1.5 inline-flex min-w-5 items-center justify-center rounded-full bg-secondary-400 px-1 text-xs font-semibold text-primary-950" aria-hidden="true">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </button>
                    <div x-cloak x-show="me" x-transition.opacity
                         class="nav-menu absolute bottom-full right-0 z-10 mb-3 w-64 overflow-hidden rounded-2xl border border-line bg-surface p-1.5 shadow-card-hover">
                        <a href="{{ route('notifications.index') }}" class="flex items-center justify-between gap-2 rounded-btn px-3 py-2.5 font-medium text-ink hover:bg-bg-alt">
                            <span class="flex items-center gap-2"><x-icon name="bell" class="size-4" /> Notifications</span>
                            @if ($unreadCount)
                                <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-secondary-400 px-1.5 text-xs font-semibold text-primary-950">{{ $unreadCount }}</span>
                            @endif
                        </a>
                        @if ($me->profile)
                            <a href="{{ route('devs.show', $me) }}" class="flex items-center gap-2 rounded-btn px-3 py-2.5 font-medium text-ink hover:bg-bg-alt">
                                <x-icon name="user" class="size-4" /> Mon profil
                            </a>
                            <a href="{{ route('profile.edit', $me) }}" class="flex items-center gap-2 rounded-btn px-3 py-2.5 font-medium text-ink hover:bg-bg-alt">
                                <x-icon name="pencil" class="size-4" /> Modifier mon profil
                            </a>
                        @endif
                        @can('moderate')
                            <a href="{{ route('moderation.index') }}" class="flex items-center gap-2 rounded-btn px-3 py-2.5 font-medium text-ink hover:bg-bg-alt">
                                <x-icon name="alert" class="size-4" /> Modération
                            </a>
                        @endcan
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-btn px-3 py-2.5 text-left font-medium text-ink hover:bg-bg-alt">
                                <x-icon name="log-out" class="size-4" /> Se déconnecter
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ $meUrl }}" @if ($meActive) aria-current="page" @endif
                       @class([
                           'flex h-14 flex-col items-center justify-center gap-0.5 rounded-xl text-xs font-medium transition',
                           'bg-surface text-primary-600 shadow-soft ring-1 ring-secondary-400 dark:text-secondary-400' => $meActive,
                           'text-ink-muted' => ! $meActive,
                       ])>
                        <x-icon name="user" class="size-5" />
                        Moi
                    </a>
                @endauth
            </li>
        </ul>

        @if ($publishUrl && $me)
            {{-- Masqué quand la page a sa propre barre d'action (fiche mission), pour ne pas la chevaucher. --}}
            <a href="{{ $publishUrl }}" data-fab class="btn-amber absolute -top-16 right-1 [body:has([data-action-bar])_&]:hidden inline-flex size-13 items-center justify-center rounded-2xl" aria-label="Publier une mission">
                <x-icon name="plus" class="size-6" />
            </a>
        @endif
    </nav>

    @livewireScripts
</body>
</html>
