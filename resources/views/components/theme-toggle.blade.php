{{-- Bascule clair / sombre. La classe initiale est posée par le script anti-flash du <head>. --}}
<button type="button"
        x-data="{ dark: document.documentElement.classList.contains('dark') }"
        x-on:click="dark = ! dark; document.documentElement.classList.toggle('dark', dark); try { localStorage.setItem('theme', dark ? 'dark' : 'light') } catch (e) {}"
        x-bind:aria-pressed="dark.toString()"
        aria-label="Basculer le thème sombre"
        {{ $attributes->merge(['class' => 'inline-flex size-11 items-center justify-center rounded-btn text-ink-muted hover:bg-secondary-soft hover:text-secondary']) }}>
    <x-icon name="moon" class="size-5 dark:hidden" />
    <x-icon name="sun" class="hidden size-5 dark:block" />
</button>
