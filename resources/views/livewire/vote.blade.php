<div class="flex flex-col items-center gap-1" x-data>
    @php($label = $votable instanceof App\Models\Answer ? 'cette réponse' : 'cette question')
    <button type="button" wire:click="vote(1)" aria-label="Voter pour {{ $label }}" aria-pressed="{{ $myVote === 1 ? 'true' : 'false' }}"
            @class([
                'inline-flex size-10 items-center justify-center rounded-[10px] border transition',
                'border-primary-600 bg-primary-600 text-white dark:border-secondary-400 dark:bg-secondary-400 dark:text-primary-950' => $myVote === 1,
                'border-line bg-surface text-ink-soft hover:border-primary-300 hover:text-primary-600 dark:hover:text-primary-300' => $myVote !== 1,
            ])>
        <x-icon name="chevron-up" class="size-5" />
    </button>

    <span class="font-serif text-xl font-medium tabular-nums text-ink" aria-live="polite">{{ $votable->score }}</span>

    <span class="relative" x-data="{ tip: false }">
        <button type="button" wire:click="vote(-1)" aria-label="Voter contre {{ $label }}" aria-pressed="{{ $myVote === -1 ? 'true' : 'false' }}"
                @unless ($canDownvote) title="Le vote négatif est ouvert à partir de 50 cauris." x-on:mouseenter="tip = true" x-on:mouseleave="tip = false" x-on:focus="tip = true" x-on:blur="tip = false" @endunless
                @class([
                    'inline-flex size-10 items-center justify-center rounded-[10px] border transition',
                    'border-secondary-500 bg-secondary-100 text-secondary-800 dark:bg-secondary-950 dark:text-secondary-300' => $myVote === -1,
                    'border-line bg-surface text-ink-soft hover:border-secondary-500' => $myVote !== -1 && $canDownvote,
                    'cursor-help border-line bg-surface text-ink-muted opacity-60' => ! $canDownvote && $myVote !== -1,
                ])>
            <x-icon name="chevron-down" class="size-5" />
        </button>
        @unless ($canDownvote)
            <span x-cloak x-show="tip" role="tooltip"
                  class="absolute left-12 top-1/2 z-10 w-56 -translate-y-1/2 rounded-[10px] border border-line bg-surface p-2.5 text-left text-xs text-ink-soft shadow-card-hover">
                Le vote négatif est ouvert à partir de 50 cauris, pour garder les échanges bienveillants.
            </span>
        @endunless
    </span>

    @if ($message)
        <p class="mt-1 max-w-28 text-center text-xs text-secondary-800 dark:text-secondary-300" role="alert">{{ $message }}</p>
    @endif
</div>
