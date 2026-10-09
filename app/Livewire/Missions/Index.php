<?php

namespace App\Livewire\Missions;

use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Models\Skill;
use App\Support\Countries;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Missions')]
#[Layout('components.layouts.app', ['search' => false])]
class Index extends Component
{
    use WithPagination;

    public const PER_PAGE = 12;

    /** Valeur du filtre pays pour les missions à distance. */
    public const REMOTE = 'teletravail';

    #[Url(as: 'competence', except: '')]
    public string $skill = '';

    #[Url(as: 'pays', except: '')]
    public string $country = '';

    #[Url(as: 'budget_min', except: '')]
    public string $budgetMin = '';

    #[Url(as: 'budget_max', except: '')]
    public string $budgetMax = '';

    #[Url(as: 'statut', except: 'open')]
    public string $status = 'open';

    #[Url(as: 'tri', except: 'recentes')]
    public string $sort = 'recentes';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('skill', 'country', 'budgetMin', 'budgetMax', 'status', 'sort');
        $this->resetPage();
    }

    #[Computed]
    public function hasFilters(): bool
    {
        return collect([$this->skill, $this->country, $this->budgetMin, $this->budgetMax])->filter()->isNotEmpty()
            || $this->status !== 'open';
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function countryOptions(): array
    {
        $present = Mission::query()->whereNotNull('country')->distinct()->pluck('country')->all();

        return [self::REMOTE => 'Télétravail'] + array_intersect_key(Countries::all(), array_flip($present));
    }

    /**
     * @return Collection<int, Skill>
     */
    #[Computed]
    public function skillOptions(): Collection
    {
        return Skill::query()->whereHas('missions')->orderBy('name')->get(['name', 'slug']);
    }

    #[Computed]
    public function missions(): LengthAwarePaginator
    {
        $status = MissionStatus::tryFrom($this->status);
        $min = $this->amount($this->budgetMin);
        $max = $this->amount($this->budgetMax);

        return Mission::query()
            ->when($status, fn (Builder $q, MissionStatus $status) => $q->where('status', $status))
            ->when($this->skill !== '', fn (Builder $q) => $q->whereHas('skills', fn (Builder $s) => $s->where('slug', $this->skill)))
            ->when($this->country === self::REMOTE, fn (Builder $q) => $q->whereNull('country'))
            ->when($this->country !== '' && $this->country !== self::REMOTE, fn (Builder $q) => $q->where('country', $this->country))
            ->when($min, fn (Builder $q, int $min) => $q->where('budget_fcfa', '>=', $min))
            ->when($max, fn (Builder $q, int $max) => $q->where('budget_fcfa', '<=', $max))
            ->with(['skills', 'client.profile'])
            ->withCount('applications')
            ->when($this->sort === 'budget',
                fn (Builder $q) => $q->orderByDesc('budget_fcfa')->latest(),
                fn (Builder $q) => $q->latest()->orderByDesc('id'))
            ->paginate(self::PER_PAGE);
    }

    private function amount(string $value): ?int
    {
        $digits = preg_replace('/\D/', '', $value);

        return $digits === '' ? null : min((int) $digits, 100_000_000);
    }

    public function paginationView(): string
    {
        return 'components.pagination';
    }

    public function render(): View
    {
        return view('livewire.missions.index', [
            'statuses' => ['open' => 'Ouvertes', 'assigned' => 'En cours', 'completed' => 'Terminées', 'tous' => 'Toutes'],
        ]);
    }
}
