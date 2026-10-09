<?php

namespace App\Models;

use App\Models\Concerns\Moderatable;
use App\Models\Concerns\RendersMarkdown;
use Database\Factories\AnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Answer extends Model
{
    /** @use HasFactory<AnswerFactory> */
    use HasFactory, RendersMarkdown;

    use Moderatable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'body',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'integer',
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
     * @return BelongsTo<Question, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphMany<Vote, $this>
     */
    public function votes(): MorphMany
    {
        return $this->morphMany(Vote::class, 'votable');
    }
}
