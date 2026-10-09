{{-- Cadre « repères » : fin cadre avec de petits carrés aux 4 coins. Un seul par page. --}}
<div {{ $attributes->merge(['class' => 'relative border border-primary-300 dark:border-primary-800']) }}>
    @foreach (['-left-1 -top-1', '-right-1 -top-1', '-bottom-1 -left-1', '-bottom-1 -right-1'] as $corner)
        <span class="absolute {{ $corner }} size-2 bg-primary-300 dark:bg-primary-700" aria-hidden="true"></span>
    @endforeach
    {{ $slot }}
</div>
