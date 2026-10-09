@props(['type', 'content', 'owner'])

{{-- Signaler (discret) : pour un membre connecté, sauf sur son propre contenu. Formulaire replié dans un <details>. --}}
@auth
    @if (auth()->id() !== $owner)
        <details {{ $attributes->class('group text-sm') }} data-report>
            <summary class="inline-flex min-h-11 cursor-pointer list-none items-center gap-1.5 text-ink-muted hover:text-ink [&::-webkit-details-marker]:hidden">
                <x-icon name="alert" class="size-4" /> Signaler
            </summary>
            <form method="post" action="{{ route('reports.store') }}" class="mt-2 max-w-sm space-y-3 rounded-2xl border border-line bg-bg-alt p-4">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="id" value="{{ $content->getKey() }}">
                <fieldset>
                    <legend class="mb-2 font-medium text-ink">Pourquoi signalez-vous ce contenu ?</legend>
                    <div class="space-y-1">
                        @foreach (App\Enums\ReportReason::options() as $value => $label)
                            <label class="flex min-h-9 items-center gap-2 text-ink">
                                <input type="radio" name="reason" value="{{ $value }}" required class="size-4 accent-primary-600">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <x-button type="submit" variant="secondary" size="sm">Envoyer le signalement</x-button>
            </form>
        </details>
    @endif
@endauth
