<?php

namespace App\Livewire\Missions;

use App\Services\PulseService;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Estimateur de budget de /missions/publier : à mesure qu'on saisit les compétences, le budget médian
 * des missions comparables (mêmes compétences, même pays si possible). Rien sous 3 missions.
 * Le champ « skills » est envoyé avec le formulaire de publication (il est rendu ici).
 */
class BudgetEstimator extends Component
{
    public string $skills = '';

    public ?string $country = null;

    /** @var list<string> */
    public array $suggestedSkills = [];

    public function mount(?string $skills = null, ?string $country = null, array $suggestedSkills = []): void
    {
        $this->skills = (string) $skills;
        $this->country = $country ?: null;
        $this->suggestedSkills = $suggestedSkills;
    }

    /** Pays choisi dans le formulaire (null pour le télétravail). */
    #[On('mission-country-changed')]
    public function setCountry(?string $country = null): void
    {
        $this->country = $country ?: null;
    }

    /**
     * @return list<string>
     */
    #[Computed]
    public function skillNames(): array
    {
        return collect(explode(',', $this->skills))->map(fn (string $s) => Str::squish($s))->filter()->unique()->take(8)->values()->all();
    }

    /**
     * @return array{median: int, count: int, skills: list<string>, country: string|null}|null
     */
    #[Computed]
    public function estimate(): ?array
    {
        $pulse = app(PulseService::class);
        $names = $this->skillNames;

        // D'abord dans le même pays, sinon partout.
        foreach (array_unique([$this->country, null]) as $country) {
            if ($result = $pulse->medianFor($names, $country)) {
                return $result + ['skills' => $names, 'country' => $country];
            }
        }

        return null;
    }

    public function render(): View
    {
        return view('livewire.missions.budget-estimator');
    }
}
