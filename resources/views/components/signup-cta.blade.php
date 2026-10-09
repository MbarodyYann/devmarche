@props(['align' => 'center', 'onDark' => false, 'recruit' => true])

{{-- Bloc CTA visiteur (11-redaction.md) : le CTA principal unique, le lien discret pour qui recrute,
     et la réassurance. Mêmes libellés partout (App\Support\Copy). --}}
<div {{ $attributes->class(['flex flex-col gap-3', 'items-center text-center' => $align === 'center', 'items-start' => $align !== 'center']) }}>
    <x-button :href="route('register')" :variant="$onDark ? 'cta' : 'primary'" size="lg" class="w-full sm:w-auto" data-cta="principal">{{ App\Support\Copy::SIGNUP_CTA }}</x-button>
    @if ($recruit)
        <a href="{{ route('missions.create') }}" @class([
            'text-[15px] underline-offset-4 hover:underline',
            'text-white/70 hover:text-white' => $onDark,
            'text-ink-muted hover:text-ink' => ! $onDark,
        ])>{{ App\Support\Copy::RECRUIT_LINK }}</a>
    @endif
    <x-reassurance :on-dark="$onDark" :items="App\Support\Copy::SIGNUP_REASSURANCE" />
</div>
