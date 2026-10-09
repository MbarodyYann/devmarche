<?php

namespace App\Livewire\Questions;

use App\Models\Question;
use App\Models\Skill;
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

#[Title('Questions-réponses')]
#[Layout('components.layouts.app', ['search' => false, 'pageHero' => [
    'start' => 'Votre question ne se perdra plus',
    'end' => 'dans un groupe WhatsApp.',
    'subtitle' => 'Posez-la en français avec les bons tags : la communauté répond et vote, et la meilleure réponse reste trouvable par tous.',
    'action' => ['route' => 'questions.create', 'label' => 'Poser ma question'],
    'recruit' => false,
]])]
class Index extends Component
{
    use WithPagination;

    public const PER_PAGE = 15;

    #[Url(except: '')]
    public string $q = '';

    #[Url(as: 'tag', except: '')]
    public string $tag = '';

    /** toutes, sans-reponse, resolues */
    #[Url(as: 'statut', except: 'toutes')]
    public string $status = 'toutes';

    /** recentes, votes, sans-reponse */
    #[Url(as: 'tri', except: 'recentes')]
    public string $sort = 'recentes';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('q', 'tag', 'status', 'sort');
        $this->resetPage();
    }

    #[Computed]
    public function hasFilters(): bool
    {
        return $this->q !== '' || $this->tag !== '' || $this->status !== 'toutes';
    }

    /**
     * @return Collection<int, Skill>
     */
    #[Computed]
    public function tagOptions(): Collection
    {
        return Skill::query()->whereHas('questions')->orderBy('name')->get(['name', 'slug']);
    }

    #[Computed]
    public function questions(): LengthAwarePaginator
    {
        $search = Str::squish($this->q);

        $query = Question::query()
            ->when($search !== '', fn (Builder $q) => $q->search($search))
            ->when($this->tag !== '', fn (Builder $q) => $q->whereHas('skills', fn (Builder $s) => $s->where('slug', $this->tag)))
            ->when($this->status === 'sans-reponse', fn (Builder $q) => $q->where('answers_count', 0))
            ->when($this->status === 'resolues', fn (Builder $q) => $q->whereNotNull('accepted_answer_id'))
            ->with(['skills', 'user' => fn ($q) => $q->withLevel(), 'user.profile']);

        match ($this->sort) {
            'votes' => $query->orderByDesc('score')->latest(),
            'sans-reponse' => $query->orderBy('answers_count')->latest(),
            default => $query->latest()->orderByDesc('id'),
        };

        return $query->paginate(self::PER_PAGE);
    }

    public function paginationView(): string
    {
        return 'components.pagination';
    }

    public function render(): View
    {
        return view('livewire.questions.index');
    }
}
