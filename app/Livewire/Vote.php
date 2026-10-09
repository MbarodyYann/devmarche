<?php

namespace App\Livewire;

use App\Models\Answer;
use App\Models\Question;
use App\Policies\VotePolicy;
use App\Services\VoteService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Boutons de vote +1 / −1 d'une question ou d'une réponse.
 */
class Vote extends Component
{
    #[Locked]
    public Question|Answer $votable;

    /** Vote actuel du membre connecté : 1, -1 ou 0. */
    public int $myVote = 0;

    public ?string $message = null;

    public function mount(Question|Answer $votable): void
    {
        $this->votable = $votable;
        $this->myVote = (int) (auth()->check()
            ? $votable->votes()->where('user_id', auth()->id())->value('value')
            : 0);
    }

    public function vote(int $value, VoteService $votes): void
    {
        $this->message = null;

        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        $response = Gate::inspect('vote', [$this->votable, $value]);
        if ($response->denied()) {
            $this->message = $response->message();

            return;
        }

        if (RateLimiter::tooManyAttempts($key = 'votes:'.auth()->id(), 30)) {
            $this->message = 'Doucement : réessayez dans une minute.';

            return;
        }
        RateLimiter::hit($key, 60);

        $this->myVote = $votes->vote(auth()->user(), $this->votable, $value);
        $this->votable->refresh();
    }

    public function render(): View
    {
        return view('livewire.vote', [
            'canDownvote' => (auth()->user()?->reputation ?? 0) >= VotePolicy::DOWNVOTE_MIN_REPUTATION,
        ]);
    }
}
