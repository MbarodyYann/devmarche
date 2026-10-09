<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function show(Question $question): View
    {
        $question->load(['user' => fn ($q) => $q->withLevel(), 'user.profile', 'skills']);

        // L'acceptée en premier, puis par score, puis par ancienneté.
        $answers = $question->answers()
            ->with('user.profile')
            ->orderByRaw('case when id = ? then 0 else 1 end', [$question->accepted_answer_id ?? 0])
            ->orderByDesc('score')
            ->oldest()
            ->get();

        return view('questions.show', ['question' => $question, 'answers' => $answers]);
    }
}
