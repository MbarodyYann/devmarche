<?php

namespace App\Models;

use App\Models\Concerns\RendersMarkdown;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory, RendersMarkdown;

    public const MAX_TAGS = 5;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'body',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'answers_count' => 'integer',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function markdownFields(): array
    {
        return ['body' => 'body_html'];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Answer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * @return BelongsTo<Answer, $this>
     */
    public function acceptedAnswer(): BelongsTo
    {
        return $this->belongsTo(Answer::class, 'accepted_answer_id');
    }

    /**
     * Tags de la question (les compétences, partagées avec les profils et les missions).
     *
     * @return BelongsToMany<Skill, $this>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class)->orderBy('name');
    }

    /**
     * @return MorphMany<Vote, $this>
     */
    public function votes(): MorphMany
    {
        return $this->morphMany(Vote::class, 'votable');
    }

    public function isResolved(): bool
    {
        return $this->accepted_answer_id !== null;
    }

    /**
     * Recherche plein texte en français (index GIN sur titre + corps).
     *
     * @param  Builder<Question>  $query
     */
    public function scopeSearch(Builder $query, string $terms): void
    {
        $query->whereRaw("to_tsvector('french', title || ' ' || body) @@ websearch_to_tsquery('french', ?)", [$terms]);
    }
}
