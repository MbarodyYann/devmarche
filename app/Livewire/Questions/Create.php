<?php

namespace App\Livewire\Questions;

use App\Models\Question;
use App\Models\Skill;
use App\Services\AnswerService;
use App\Services\MarkdownRenderer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Poser ma question')]
class Create extends Component
{
    public string $title = '';

    public string $body = '';

    /** Tags séparés par des virgules. */
    public string $tags = '';

    public function mount(): void
    {
        Gate::authorize('create', Question::class);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:15', 'max:150'],
            'body' => ['required', 'string', 'min:30', 'max:10000'],
            'tags' => ['nullable', 'string', 'max:300'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'title.min' => 'Résumez le problème en une phrase d\'au moins 15 caractères.',
            'body.min' => 'Décrivez votre problème en au moins 30 caractères : ce que vous avez essayé, le message d\'erreur.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return ['title' => 'titre', 'body' => 'description', 'tags' => 'tags'];
    }

    /**
     * @return list<string>
     */
    private function tagNames(): array
    {
        return collect(explode(',', $this->tags))
            ->map(fn (string $tag) => Str::squish($tag))
            ->filter()
            ->unique(fn (string $tag) => Str::slug($tag))
            ->values()
            ->all();
    }

    #[Computed]
    public function preview(): string
    {
        return trim($this->body) === '' ? '' : app(MarkdownRenderer::class)->toHtml($this->body);
    }

    public function save(AnswerService $service): void
    {
        Gate::authorize('create', Question::class);
        $this->validate();

        $tags = $this->tagNames();
        if (count($tags) > Question::MAX_TAGS) {
            $this->addError('tags', 'Indiquez au maximum '.Question::MAX_TAGS.' tags.');

            return;
        }
        if (collect($tags)->contains(fn (string $tag) => mb_strlen($tag) > 40)) {
            $this->addError('tags', 'Chaque tag doit faire 40 caractères au maximum.');

            return;
        }

        if (RateLimiter::tooManyAttempts($key = 'questions:'.auth()->id(), 10)) {
            $this->addError('title', 'Vous avez posé beaucoup de questions cette heure-ci : réessayez un peu plus tard.');

            return;
        }
        RateLimiter::hit($key, 3600);

        $question = $service->ask(auth()->user(), Str::squish($this->title), trim($this->body), $tags);

        session()->flash('status', 'Question publiée. La communauté va vous répondre : vous verrez les réponses ici.');
        $this->redirectRoute('questions.show', $question);
    }

    public function render(): View
    {
        return view('livewire.questions.create', [
            'suggestedTags' => Skill::query()->orderBy('name')->pluck('name'),
        ]);
    }
}
