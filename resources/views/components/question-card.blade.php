@props(['question'])

{{-- Carte question (v3) : titre, extrait, tags, réponses, score, « Résolue », auteur avec cauris. --}}
<x-card :href="route('questions.show', $question)" padding="p-5 sm:p-6" {{ $attributes->merge(['class' => 'flex min-w-0 flex-col gap-3']) }}>
    <div class="flex flex-wrap items-center gap-2">
        @if ($question->isResolved())
            <x-chip tone="done">Résolue</x-chip>
        @elseif ($question->answers_count === 0)
            <x-chip tone="warm">Sans réponse</x-chip>
        @endif
        <span class="text-sm text-ink-muted">{{ $question->answers_count }} {{ $question->answers_count > 1 ? 'réponses' : 'réponse' }} · {{ $question->score }} {{ abs($question->score) > 1 ? 'votes' : 'vote' }}</span>
    </div>

    <h3 class="text-[22px] leading-snug">{{ $question->title }}</h3>
    <p class="line-clamp-2 text-ink-soft">{{ App\Support\Text::excerpt($question->body_html, 220) }}</p>

    <div class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-1">
        <x-skills :skills="$question->skills" :limit="4" :link="false" />
        <span class="flex min-w-0 items-center gap-2 text-sm text-ink-muted">
            <x-avatar :user="$question->user" size="xs" />
            <span class="truncate">{{ $question->user->name }}</span>
            <x-reputation :points="$question->user->reputation" />
            @if ($question->user->isNewMember())
                <x-chip tone="warm" class="px-2! py-0.5! text-xs!">Nouveau</x-chip>
            @endif
            <span class="shrink-0 whitespace-nowrap">· {{ $question->created_at->translatedFormat('j M') }}</span>
        </span>
    </div>
</x-card>
