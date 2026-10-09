<x-layouts.app :title="$question->title" :description="$question->body_html">
    @php
        $user = auth()->user();
        $isAuthor = $user && $user->id === $question->user_id;
    @endphp

    <a href="{{ route('questions.index') }}" class="mb-4 inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-ink">
        <x-icon name="arrow-left" class="size-4" /> Toutes les questions
    </a>

    <div class="mx-auto max-w-4xl space-y-6">
        {{-- La question --}}
        <x-card>
            <div class="flex gap-4 sm:gap-6">
                <livewire:vote :votable="$question" :key="'vote-q-'.$question->id" />
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($question->isResolved())
                            <x-chip tone="done">Résolue</x-chip>
                        @elseif ($question->answers_count === 0)
                            <x-chip tone="warm">Sans réponse</x-chip>
                        @endif
                        <span class="text-sm text-ink-muted">Posée le {{ $question->created_at->translatedFormat('j M Y') }}</span>
                    </div>
                    <h1 class="mt-3 break-words text-[30px] sm:text-[40px]">{{ $question->title }}</h1>
                    <div class="prose-dm mt-5">{!! $question->body_html !!}</div>
                    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-line pt-5">
                        <x-skills :skills="$question->skills" />
                        <a href="{{ route('devs.show', $question->user) }}" class="flex min-w-0 items-center gap-2 text-sm">
                            <x-avatar :user="$question->user" size="sm" />
                            <span class="min-w-0">
                                <span class="block truncate font-semibold text-ink">{{ $question->user->name }}</span>
                                <x-reputation :points="$question->user->reputation" />
                            </span>
                            @if ($question->user->isNewMember())
                                <x-new-badge />
                            @endif
                        </a>
                    </div>
                    <x-share class="mt-5" :title="$question->title" :url="route('questions.show', $question)" />
                    <x-report-button type="question" :content="$question" :owner="$question->user_id" class="mt-2" />
                </div>
            </div>
        </x-card>

        {{-- Les réponses --}}
        <section aria-labelledby="titre-reponses">
            <h2 id="titre-reponses" class="mb-4 text-[28px]">{{ $answers->count() }} {{ $answers->count() > 1 ? 'réponses' : 'réponse' }}</h2>

            @if ($answers->isEmpty())
                <x-card padding="p-6">
                    <p class="text-ink-soft">Pas encore de réponse. Vous connaissez la solution ? Votre réponse aidera tous ceux qui chercheront la même chose.</p>
                </x-card>
            @else
                <ul class="space-y-4">
                    @foreach ($answers as $answer)
                        @php($accepted = $answer->id === $question->accepted_answer_id)
                        <li id="reponse-{{ $answer->id }}" class="scroll-mt-28">
                            <x-card @class(['border-l-4 border-l-primary-600 dark:border-l-secondary-400' => $accepted])>
                                <div class="flex gap-4 sm:gap-6">
                                    <livewire:vote :votable="$answer" :key="'vote-a-'.$answer->id" />
                                    <div class="min-w-0 flex-1">
                                        @if ($accepted)
                                            <x-chip tone="done" class="mb-3">Acceptée</x-chip>
                                        @endif
                                        <div class="prose-dm">{!! $answer->body_html !!}</div>
                                        <div class="mt-5 flex flex-wrap items-center justify-between gap-4 border-t border-line pt-4">
                                            <a href="{{ route('devs.show', $answer->user) }}" class="flex min-w-0 items-center gap-2 text-sm">
                                                <x-avatar :user="$answer->user" size="sm" />
                                                <span class="min-w-0">
                                                    <span class="block truncate font-semibold text-ink">{{ $answer->user->name }}</span>
                                                    <span class="text-ink-muted">{{ $answer->created_at->translatedFormat('j M Y') }}</span>
                                                </span>
                                                <x-reputation :points="$answer->user->reputation" />
                                            </a>
                                            @can('accept', [$question, $answer])
                                                <form method="post" action="{{ route('answers.accept', [$question, $answer]) }}">
                                                    @csrf
                                                    <x-button type="submit" :variant="$accepted ? 'secondary' : 'primary'" size="sm">
                                                        @if ($accepted)
                                                            Retirer l'acceptation
                                                        @else
                                                            <x-icon name="check" class="size-4" /> Accepter cette réponse
                                                        @endif
                                                    </x-button>
                                                </form>
                                            @endcan
                                        </div>
                                        <x-report-button type="answer" :content="$answer" :owner="$answer->user_id" class="mt-2" />
                                    </div>
                                </div>
                            </x-card>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Répondre --}}
        <x-card id="repondre">
            <h2 class="text-[28px]">Votre réponse</h2>
            @auth
                <form method="post" action="{{ route('answers.store', $question) }}" class="mt-5 space-y-4" novalidate>
                    @csrf
                    <x-field label="Réponse" name="body" hint="Markdown accepté : expliquez la démarche et montrez le code utile.">
                        <x-textarea name="body" rows="8" maxlength="10000" class="font-mono text-sm" required />
                    </x-field>
                    <div class="flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-ink-muted">Une réponse votée vous rapporte +10 cauris, une réponse acceptée +15.</p>
                        <x-button type="submit" class="w-full sm:w-auto">Publier ma réponse</x-button>
                    </div>
                </form>
            @else
                <p class="mt-3 text-ink-soft">Vous connaissez la réponse ? Rejoignez la communauté pour la partager.</p>
                <x-signup-cta class="mt-5" align="start" :recruit="false" />
                <p class="mt-4 text-sm"><a href="{{ route('login') }}" class="text-ink-muted underline-offset-4 hover:text-ink hover:underline">Déjà membre ? Se connecter</a></p>
            @endauth
        </x-card>
    </div>
</x-layouts.app>
