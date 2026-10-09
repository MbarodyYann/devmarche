<?php

namespace App\Models;

use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Avis du client sur le freelance d'une mission terminée : note globale et 3 critères (1 à 5).
 * Ne pas créer directement : passer par ReviewService (bonus de réputation).
 */
class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    /** Les 3 critères, dans l'ordre d'affichage. */
    public const CRITERIA = [
        'communication' => 'Communication',
        'quality' => 'Qualité du travail',
        'timeliness' => 'Respect des délais',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rating',
        'communication',
        'quality',
        'timeliness',
        'comment',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'communication' => 'integer',
            'quality' => 'integer',
            'timeliness' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Mission, $this>
     */
    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }
}
