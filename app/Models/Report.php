<?php

namespace App\Models;

use App\Enums\ReportReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Signalement d'un contenu (mission, question, réponse) par un membre.
 */
class Report extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = ['reason'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['reason' => ReportReason::class];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function reportable(): MorphTo
    {
        // Le contenu masqué reste accessible à la modération.
        return $this->morphTo()->withoutGlobalScope('visible');
    }
}
