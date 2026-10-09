@props(['amount', 'size' => 'md'])

{{-- Budget d'une mission : gros chiffre en Newsreader, étiquette « FCFA / mission » dessous (comme « per seat/month »). --}}
<div {{ $attributes->merge(['class' => 'flex flex-col']) }}>
    <span @class([
        'font-serif font-medium leading-none tracking-tight text-ink tabular-nums',
        'text-[28px]' => $size === 'md',
        'text-[44px] sm:text-[56px]' => $size === 'lg',
    ])>{{ App\Support\Money::fcfa($amount) }}</span>
    <span class="mt-1.5 text-sm text-ink-muted">FCFA / mission</span>
</div>
