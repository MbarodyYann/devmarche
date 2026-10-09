{{-- Pagination Livewire aux couleurs de DevMarché. --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between gap-4">
        <p class="text-sm text-ink-muted">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }}
        </p>

        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="inline-flex min-h-11 items-center gap-1 rounded-btn px-3 text-ink-muted opacity-50" aria-disabled="true">
                    <x-icon name="arrow-left" class="size-4" /> Précédent
                </span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                        class="inline-flex min-h-11 items-center gap-1 rounded-btn border border-border bg-surface px-3 font-semibold hover:border-secondary">
                    <x-icon name="arrow-left" class="size-4" /> Précédent
                </button>
            @endif

            <span class="px-2 text-sm text-ink-muted">Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                        class="inline-flex min-h-11 items-center gap-1 rounded-btn border border-border bg-surface px-3 font-semibold hover:border-secondary">
                    Suivant <x-icon name="arrow-right" class="size-4" />
                </button>
            @else
                <span class="inline-flex min-h-11 items-center gap-1 rounded-btn px-3 text-ink-muted opacity-50" aria-disabled="true">
                    Suivant <x-icon name="arrow-right" class="size-4" />
                </span>
            @endif
        </div>
    </nav>
@endif
