<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Mission;
use App\Models\Question;
use App\Models\Report;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * File de modération (is_admin) : contenus signalés, du plus signalé au moins signalé ; masquer ou rétablir.
 */
class ModerationController extends Controller
{
    private const TYPES = ['mission' => Mission::class, 'question' => Question::class, 'answer' => Answer::class];

    public function index(): View
    {
        $items = Report::query()
            ->with(['reportable' => fn (MorphTo $morph) => $morph->morphWith([
                Mission::class => ['client:id,name'],
                Question::class => ['user:id,name'],
                Answer::class => ['user:id,name'],
            ]), 'reporter:id,name'])
            ->latest()
            ->get()
            ->groupBy(fn (Report $r) => $r->reportable_type.':'.$r->reportable_id)
            ->map(fn ($reports) => [
                'type' => $type = $reports->first()->reportable_type,
                'content' => $content = $reports->first()->reportable,
                'reports' => $reports,
                'link' => $content === null ? null : match ($type) {
                    'mission' => route('missions.show', $content->getKey()),
                    'question' => route('questions.show', $content->getKey()),
                    default => route('questions.show', $content->question_id).'#reponse-'.$content->getKey(),
                },
            ])
            ->filter(fn (array $item) => $item['content'] !== null)
            ->sortBy([
                fn ($a, $b) => $a['content']->isHidden() <=> $b['content']->isHidden(),
                fn ($a, $b) => $b['reports']->count() <=> $a['reports']->count(),
            ])
            ->values();

        return view('moderation.index', ['items' => $items]);
    }

    public function hide(string $type, int $id): RedirectResponse
    {
        $content = $this->find($type, $id);
        $content->forceFill(['hidden_at' => now()])->save();

        return back()->with('status', 'Contenu masqué : il n\'est plus visible du public.');
    }

    public function restore(string $type, int $id): RedirectResponse
    {
        $content = $this->find($type, $id);
        $content->forceFill(['hidden_at' => null])->save();

        return back()->with('status', 'Contenu rétabli : il est de nouveau visible.');
    }

    private function find(string $type, int $id): Model
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        return self::TYPES[$type]::withoutGlobalScope('visible')->findOrFail($id);
    }
}
