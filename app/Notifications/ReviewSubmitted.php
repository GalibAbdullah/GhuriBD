<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Notifications\Notification;

class ReviewSubmitted extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Review $review,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $name = $this->review->resort?->name ?? $this->review->tourPackage?->title;

        return [
            'title' => 'Review submitted',
            'message' => 'Thanks for reviewing '.$name.'. Your feedback helps other travelers.',
            'action_url' => $this->review->resort
                ? route('traveler.resorts.show', $this->review->resort)
                : route('traveler.packages.show', $this->review->tourPackage),
            'review_id' => $this->review->id,
        ];
    }
}
