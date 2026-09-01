<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

class BookingCreated extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Booking $booking,
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
        return [
            'title' => 'Booking confirmed',
            'message' => 'Your booking '.$this->booking->booking_reference.' has been received and is now pending confirmation.',
            'action_url' => route('traveler.bookings.show', $this->booking),
            'booking_id' => $this->booking->id,
        ];
    }
}
