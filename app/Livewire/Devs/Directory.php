<?php

namespace App\Livewire\Devs;

use App\Enums\Availability;
use App\Enums\DevLevel;
use App\Models\Profile;
use App\Models\Skill;
use App\Models\User;
use App\Support\Countries;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Annuaire des devs')]
#[Layout('components.layouts.app', ['pageHero' => [
    'start' => 'Le bon dev est peut-être',
    'end' => 'dans votre ville.',
    'subtitle' => 'Filtrez par compétence, pays et disponibilité : les cauris montrent qui a déjà livré des missions et aidé la communauté.',
    'action' => null,
]])]
class Directory extends Component
{
    use WithPagination;

    public const PER_PAGE = 12;

    #[Url(except: '')]
    public string $q = '';

    #[Url(as: 'pays', except: '')]
    public string $country = '';

    #[Url(as: 'ville', except: '')]
    public string $city = '';

    #[Url(as: 'competence', except: '')]
    public string $skill = '';

    #[Url(as: 'dispo', except: '')]
    public string $availability = '';

    #[Url(as: 'niveau', except: '')]
    public string $level = '';

    #[Url(as: 'langue', except: '')]
    public string $language = '';

    /** Seulement les membres actifs ces 7 derniers jours. */
    #[Url(as: 'actifs', except: false)]
    public bool $activeWeek = false;

    public function updated(string $property): void
    {
        if ($property === 'country') {
            $this->city = '';
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('q', 'country', 'city', 'skill', 'availability', 'level', 'language', 'activeWeek');
        $this->resetPage();
    }

    #[Computed]
    public function hasFilters(): bool
    {
        return collect([$this->q, $this->country, $this->city, $this->skill, $this->availability, $this->level, $this->language, $this->activeWeek])->filter()->isNotEmpty();
    }

    /**
     * Pays où vivent des devs inscrits, dans l'ordre de config/countries.php.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function countryOptions(): array
    {
        $present = Profile::query()->distinct()->pluck('country')->all();

        return array_intersect_key(Countries::all(), array_flip($present));
    }

    /**
     * Langues effectivement parlées par des membres, dans l'ordre de config/languages.php.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function languageOptions(): array
    {
        $spoken = Profile::query()->whereNotNull('languages')->pluck('languages')->flatten()->unique()->all();

        return collect(config('languages'))
            ->only($spoken)
            ->map(fn (string $label) => Str::ucfirst($label))
            ->all();
    }

    /**
     * @return Collection<int, string>
     */
    #[Computed]
    public function cityOptions(): Collection
    {
        return Profile::query()
            ->when($this->country, fn (Builder $query) => $query->where('country', $this->country))
            ->distinct()
            ->orderBy('city')
            ->pluck('city');
    }

    /**
     * @return Collection<int, Skill>
     */
    #[Computed]
    public function skillOptions(): Collection
    {
        return Skill::query()->whereHas('users')->orderBy('name')->get(['name', 'slug']);
    }

    #[Computed]
    public function devs(): LengthAwarePaginator
    {
        $search = Str::squish($this->q);

        return User::query()
            ->whereHas('profile', function (Builder $query) {
                $query
                    ->when($this->country !== '', fn (Builder $q) => $q->where('country', $this->country))
                    ->when($this->city !== '', fn (Builder $q) => $q->whereRaw('lower(city) = ?', [mb_strtolower(Str::squish($this->city))]))
                    ->when(Availability::tryFrom($this->availability), fn (Builder $q, Availability $availability) => $q->where('availability', $availability))
                    ->when(array_key_exists($this->language, config('languages')), fn (Builder $q) => $q->whereJsonContains('languages', $this->language));
            })
            ->when(DevLevel::tryFrom($this->level), fn (Builder $query, DevLevel $level) => $level->scope($query))
            ->when($this->activeWeek, fn (Builder $query) => $query->where('last_seen_at', '>=', now()->subDays(7)))
            ->when($this->skill !== '', fn (Builder $query) => $query->whereHas('skills', fn (Builder $q) => $q->where('slug', $this->skill)))
            ->when($search !== '', function (Builder $query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(fn (Builder $q) => $q
                    ->where('name', 'ilike', $like)
                    ->orWhereHas('profile', fn (Builder $p) => $p->where('headline', 'ilike', $like))
                    ->orWhereHas('skills', fn (Builder $s) => $s->where('name', 'ilike', $like)));
            })
            ->with(['profile', 'skills'])
            ->withLevel()
            ->withReviewStats()
            ->orderByDesc('reputation')
            ->orderBy('name')
            ->paginate(self::PER_PAGE);
    }

    public function paginationView(): string
    {
        return 'components.pagination';
    }

    public function render(): View
    {
        return view('livewire.devs.directory', [
            'availabilities' => Availability::options(),
            'levels' => DevLevel::options(),
        ]);
    }
}
