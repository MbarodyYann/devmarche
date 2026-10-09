<?php

namespace App\Models\Concerns;

use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Contenu signalable et masquable par la modération : un contenu masqué (hidden_at) est invisible
 * du public partout (portée globale « visible ») ; la modération le retrouve avec withoutGlobalScope('visible').
 */
trait Moderatable
{
    public static function bootModeratable(): void
    {
        static::addGlobalScope('visible', fn (Builder $query) => $query->whereNull($query->qualifyColumn('hidden_at')));
    }

    public function initializeModeratable(): void
    {
        $this->mergeCasts(['hidden_at' => 'datetime']);
    }

    /**
     * @return MorphMany<Report, $this>
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function isHidden(): bool
    {
        return $this->hidden_at !== null;
    }
}
