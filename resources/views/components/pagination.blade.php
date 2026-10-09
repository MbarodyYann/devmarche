{{-- Pagination Livewire, style registre. --}}
@if ($paginator->hasPages())
    @php($pageButton = 'inline-flex min-h-11 items-center gap-1 rounded-btn border-2 border-ink bg-surface px-3 font-bold enfoncable')
    <nav role="navigation" aria-label="Pagination" class="flex flex-wrap items-center justify-between gap-4">
        <p class="etiquette text-ink-muted">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }}
        </p>

        <div class="flex items-center gap-3">
            @if ($paginator->onFirstPage())
                <span class="{{ $pageButton }} opacity-40 shadow-none!" aria-disabled="true">
                    <x-icon name="arrow-left" class="size-4" /> Préc.
                </span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="{{ $pageButton }}">
                    <x-icon name="arrow-left" class="size-4" /> Préc.
                </button>
            @endif

            <span class="etiquette">Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="{{ $pageButton }}">
                    Suiv. <x-icon name="arrow-right" class="size-4" />
                </button>
            @else
                <span class="{{ $pageButton }} opacity-40 shadow-none!" aria-disabled="true">
                    Suiv. <x-icon name="arrow-right" class="size-4" />
                </span>
            @endif
        </div>
    </nav>
@endif
