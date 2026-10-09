<x-layouts.app title="Notifications" :noindex="true">
    <div class="mx-auto max-w-2xl">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <h1 class="text-[34px] sm:text-[44px]">Notifications</h1>
            @if (auth()->user()->unreadNotifications()->exists())
                <form method="post" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <x-button type="submit" variant="secondary" size="sm">Tout marquer comme lu</x-button>
                </form>
            @endif
        </div>

        @if ($notifications->isEmpty())
            <x-empty-state class="mt-8" title="Rien de neuf pour l'instant.">
                Postulez à une mission ou répondez à une question : les réponses arriveront ici.
                <div class="mt-6">
                    <x-button :href="route('missions.index', ['pour_moi' => 1])">{{ App\Support\Copy::MEMBER_CTA }}</x-button>
                </div>
            </x-empty-state>
        @else
            <x-card padding="p-2 sm:p-3" class="mt-6">
                <ul class="divide-y divide-line">
                    @foreach ($notifications as $notification)
                        <li><x-notification-item :notification="$notification" /></li>
                    @endforeach
                </ul>
            </x-card>
            @if ($notifications->hasPages())
                <nav aria-label="Pagination" class="mt-6 flex items-center justify-between gap-4">
                    <p class="text-sm text-ink-muted">{{ $notifications->firstItem() }}–{{ $notifications->lastItem() }} sur {{ $notifications->total() }}</p>
                    <div class="flex gap-2">
                        @if ($notifications->previousPageUrl())
                            <x-button :href="$notifications->previousPageUrl()" variant="secondary" size="sm"><x-icon name="arrow-left" class="size-4" /> Plus récentes</x-button>
                        @endif
                        @if ($notifications->nextPageUrl())
                            <x-button :href="$notifications->nextPageUrl()" variant="secondary" size="sm">Plus anciennes <x-icon name="arrow-right" class="size-4" /></x-button>
                        @endif
                    </div>
                </nav>
            @endif
        @endif
    </div>
</x-layouts.app>
