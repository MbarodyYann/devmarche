<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Services\AnswerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AnswerController extends Controller
{
    public function store(Request $request, Question $question, AnswerService $answers): RedirectResponse
    {
        Gate::authorize('answer', $question);

        $data = $request->validate(
            ['body' => ['required', 'string', 'min:20', 'max:10000']],
            ['body.min' => 'Votre réponse doit faire au moins 20 caractères : expliquez, montrez du code si besoin.'],
            ['body' => 'réponse'],
        );

        $answer = $answers->answer($request->user(), $question, trim($data['body']));

        return redirect()
            ->to(route('questions.show', $question).'#reponse-'.$answer->id)
            ->with('status', 'Réponse publiée. Chaque vote positif vous rapportera +10 cauris.');
    }

    public function accept(Question $question, Answer $answer, AnswerService $answers): RedirectResponse
    {
        Gate::authorize('accept', [$question, $answer]);

        $accepted = $answers->toggleAccepted($question, $answer);

        return redirect()
            ->to(route('questions.show', $question).'#reponse-'.$answer->id)
            ->with('status', $accepted
                ? 'Réponse acceptée : elle s\'affiche en premier, et son auteur reçoit +15 cauris.'
                : 'Réponse retirée des réponses acceptées.');
    }
}
