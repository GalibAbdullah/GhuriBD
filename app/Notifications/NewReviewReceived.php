<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Notifications\Notification;

class NewReviewReceived extends Notification
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
            'title' => 'New review received',
            'message' => $this->review->user->name.' rated '.$name.' '.$this->review->rating.' out of 5.',
            'action_url' => route('partner.reviews.index'),
            'review_id' => $this->review->id,
        ];
    }
}
