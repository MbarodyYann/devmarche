@props(['notification'])

{{-- Une notification : icône, texte, date relative ; non lue = pastille ambre et texte appuyé. --}}
@php($unread = $notification->read_at === null)
<a href="{{ route('notifications.open', $notification->id) }}" {{ $attributes->class(['flex items-start gap-3 rounded-btn px-3 py-2.5 transition hover:bg-bg-alt']) }}>
    <span @class([
        'mt-0.5 inline-flex size-8 shrink-0 items-center justify-center rounded-full',
        'bg-secondary-400 text-primary-950' => $unread,
        'bg-bg-alt text-ink-muted ring-1 ring-line' => ! $unread,
    ])>
        <x-icon :name="$notification->data['icon'] ?? 'check'" class="size-4" />
    </span>
    <span class="min-w-0 flex-1">
        <span @class(['block text-sm', 'font-semibold text-ink' => $unread, 'text-ink-soft' => ! $unread])>{{ $notification->data['text'] ?? '' }}</span>
        <span class="mt-0.5 block text-xs text-ink-muted">{{ $notification->created_at->diffForHumans() }}@if ($unread) <span class="sr-only">, non lue</span>@endif</span>
    </span>
</a>
