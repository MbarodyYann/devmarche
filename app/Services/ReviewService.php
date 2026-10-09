<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\Review;
use App\Models\User;
use App\Notifications\ReviewReceived;
use Illuminate\Support\Facades\DB;

/**
 * Avis du client sur le freelance, avec le bonus de réputation (02 : 5★ = +30, 4★ = +20, 3★ = +10).
 */
class ReviewService
{
    /** Bonus de réputation selon la note globale. */
    public const BONUS = [5 => 30, 4 => 20, 3 => 10];

    public function __construct(private readonly ReputationService $reputation) {}

    /**
     * @param  array{rating: int, communication: int, quality: int, timeliness: int, comment?: string|null}  $data
     */
    public function submit(Mission $mission, User $client, array $data): Review
    {
        return DB::transaction(function () use ($mission, $client, $data) {
            $review = new Review($data);
            $review->mission()->associate($mission);
            $review->reviewer()->associate($client);
            $review->reviewee()->associate($mission->freelancer_id);
            $review->save();

            $freelancer = $mission->freelancer()->firstOrFail();
            if ($bonus = self::BONUS[$review->rating] ?? 0) {
                $this->reputation->award($freelancer, $bonus, 'review_bonus', $review);
            }

            $review->setRelation('reviewer', $client)->setRelation('mission', $mission);
            $freelancer->notify(new ReviewReceived($review));

            return $review;
        });
    }
}
