@props(['review', 'loyal' => false, 'context' => false])

{{-- Un avis : note, 3 critères, commentaire, auteur. Avec context (profil) : la mission, sa fourchette de
     budget, sa durée réelle, et « Client fidèle » quand ce client a confié au moins 2 missions au dev. --}}
<article {{ $attributes->class('flex flex-col gap-3') }} data-review>
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
        <x-stars :value="$review->rating" />
        <span class="font-semibold text-ink">{{ $review->rating }},0</span>
        @if ($loyal)
            <x-chip tone="done">Client fidèle</x-chip>
        @endif
    </div>

    @if ($review->comment)
        <p class="whitespace-pre-line text-ink-soft">{{ $review->comment }}</p>
    @endif

    <dl class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-ink-muted">
        @foreach (App\Models\Review::CRITERIA as $field => $label)
            <div class="flex items-center gap-1.5">
                <dt>{{ $label }}</dt>
                <dd class="font-semibold text-ink">{{ $review->{$field} }}/5</dd>
            </div>
        @endforeach
    </dl>

    <p class="text-sm text-ink-muted">
        {{ $review->reviewer->name }} · {{ $review->created_at->translatedFormat('j M Y') }}
        @if ($context && $review->mission)
            <br>
            <a href="{{ route('missions.show', $review->mission) }}" class="font-medium text-ink hover:underline">{{ $review->mission->title }}</a>
            · <span class="whitespace-nowrap">{{ App\Support\Money::budgetRange($review->mission->budget_fcfa) }}</span>
            @if ($review->mission->assigned_at && $review->mission->completed_at)
                · <span class="whitespace-nowrap">Livrée en {{ $days = max(1, (int) $review->mission->assigned_at->diffInDays($review->mission->completed_at)) }} {{ $days > 1 ? 'jours' : 'jour' }}</span>
            @endif
        @endif
    </p>
</article>
