<x-layouts.app title="Modération" :noindex="true">
    <div class="mx-auto max-w-3xl">
        <h1 class="text-[34px] sm:text-[44px]">Modération</h1>
        <p class="mt-2 text-ink-soft">Contenus signalés, les plus signalés en tête. Un contenu masqué disparaît du site pour tout le monde.</p>

        @if ($items->isEmpty())
            <x-empty-state class="mt-8" title="Aucun signalement en attente.">La communauté n'a rien signalé pour l'instant.</x-empty-state>
        @else
            <ul class="mt-8 space-y-4">
                @foreach ($items as $item)
                    @php($content = $item['content'])
                    <li>
                        <x-card padding="p-5" data-moderation-item="{{ $item['type'] }}-{{ $content->getKey() }}">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-chip tone="neutral">{{ ['mission' => 'Mission', 'question' => 'Question', 'answer' => 'Réponse'][$item['type']] }}</x-chip>
                                @if ($content->isHidden())
                                    <x-chip tone="muted">Masqué</x-chip>
                                @endif
                                <span class="text-sm text-ink-muted">{{ $item['reports']->count() }} {{ $item['reports']->count() > 1 ? 'signalements' : 'signalement' }}</span>
                            </div>

                            <p class="mt-3 font-semibold text-ink">
                                @switch($item['type'])
                                    @case('answer')
                                        Réponse de {{ $content->user->name }}
                                        @break
                                    @case('mission')
                                        {{ $content->title }}<span class="font-normal text-ink-muted">, par {{ $content->client->name }}</span>
                                        @break
                                    @default
                                        {{ $content->title }}<span class="font-normal text-ink-muted">, par {{ $content->user->name }}</span>
                                @endswitch
                            </p>
                            <p class="mt-1 line-clamp-3 text-sm text-ink-soft">{{ Str::limit(Str::squish(strip_tags($content->{$item['type'] === 'mission' ? 'description' : 'body'})), 280) }}</p>

                            <ul class="mt-3 flex flex-wrap gap-2 text-sm">
                                @foreach ($item['reports']->countBy(fn ($r) => $r->reason->label()) as $reason => $count)
                                    <li><x-chip tone="warm">{{ $reason }}{{ $count > 1 ? ' × '.$count : '' }}</x-chip></li>
                                @endforeach
                            </ul>

                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                @if ($content->isHidden())
                                    <form method="post" action="{{ route('moderation.restore', [$item['type'], $content->getKey()]) }}">
                                        @csrf
                                        <x-button type="submit" size="sm">Rétablir</x-button>
                                    </form>
                                @else
                                    <form method="post" action="{{ route('moderation.hide', [$item['type'], $content->getKey()]) }}">
                                        @csrf
                                        <x-button type="submit" size="sm">Masquer</x-button>
                                    </form>
                                    <a href="{{ $item['link'] }}" class="text-sm font-semibold text-primary-600 underline-offset-4 hover:underline dark:text-primary-300">Voir le contenu</a>
                                @endif
                            </div>
                        </x-card>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts.app>
