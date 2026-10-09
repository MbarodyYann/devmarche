<?php

namespace App\Notifications;

use App\Models\Review;

/** Au dev : le client a laissé un avis. */
class ReviewReceived extends AppNotification
{
    public function __construct(private readonly Review $review) {}

    public function toArray(object $notifiable): array
    {
        return [
            'text' => $this->review->reviewer->name.' vous a laissé un avis '.$this->review->rating.'★ sur « '.$this->review->mission->title.' ».',
            'url' => route('devs.show', $this->review->reviewee_id).'#avis',
            'icon' => 'star',
        ];
    }
}
