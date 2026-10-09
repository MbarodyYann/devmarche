<div>
    <x-field label="Compétences recherchées" name="skills" hint="Séparées par des virgules, 8 maximum. Ex. Laravel, Wave API" optional>
        <x-input name="skills" list="competences" autocomplete="off" wire:model.live.debounce.500ms="skills" />
        <datalist id="competences">
            @foreach ($suggestedSkills as $suggestedSkill)
                <option value="{{ $suggestedSkill }}"></option>
            @endforeach
        </datalist>
    </x-field>

    {{-- Estimation : rien sous 3 missions comparables. --}}
    <div aria-live="polite" class="mt-3" data-budget-estimate>
        @if ($estimate = $this->estimate)
            <p class="flex items-start gap-2 rounded-2xl bg-bg-alt px-4 py-3 text-sm text-ink ring-1 ring-line">
                <x-icon name="chart" class="mt-0.5 size-4 shrink-0 text-ink-muted" />
                <span>Les missions similaires ({{ implode(', ', array_filter([...$estimate['skills'], $estimate['country']])) }}) ont un budget médian de <strong class="font-semibold whitespace-nowrap">{{ App\Support\Money::fcfa($estimate['median']) }} FCFA</strong> ({{ $estimate['count'] }} missions).</span>
            </p>
        @endif
    </div>
</div>
