<?php

namespace App\Http\Controllers;

use App\Enums\ReportReason;
use App\Models\Answer;
use App\Models\Mission;
use App\Models\Question;
use App\Models\Report;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Signaler une mission, une question ou une réponse : un signalement par membre et par contenu.
 */
class ReportController extends Controller
{
    /** Types signalables (alias de la morph map). */
    private const TYPES = ['mission' => Mission::class, 'question' => Question::class, 'answer' => Answer::class];

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
            'id' => ['required', 'integer'],
            'reason' => ['required', Rule::enum(ReportReason::class)],
        ], [
            'reason.required' => 'Choisissez une raison.',
        ]);

        /** @var Model $content */
        $content = self::TYPES[$data['type']]::findOrFail($data['id']);
        $user = $request->user();

        abort_if($content->getAttribute($data['type'] === 'mission' ? 'client_id' : 'user_id') === $user->id, 403, 'Vous ne pouvez pas signaler votre propre contenu.');

        $key = ['reporter_id' => $user->id, 'reportable_type' => $data['type'], 'reportable_id' => $content->getKey()];

        if (Report::where($key)->exists()) {
            return back()->with('status', 'Vous avez déjà signalé ce contenu. La modération va le vérifier.');
        }

        (new Report(['reason' => $data['reason']]))->forceFill($key)->save();

        return back()->with('status', 'Merci, c\'est signalé. La modération va vérifier ce contenu.');
    }
}
