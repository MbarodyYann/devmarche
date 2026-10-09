<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Vote +1 / −1 sur une question ou une réponse. Ne jamais créer directement : passer par VoteService.
 */
class Vote extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'value',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function votable(): MorphTo
    {
        return $this->morphTo();
    }
}
