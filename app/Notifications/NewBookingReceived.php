<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

class NewBookingReceived extends Notification
{
    /**
     * Create a new notification instance.
     *
     * @param  string  $label  What was booked, from this partner's point of view (e.g. "resort" or "tour package").
     */
    public function __construct(
        public Booking $booking,
        public string $label,
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
            'title' => 'New booking received',
            'message' => $this->booking->user->name.' booked your '.$this->label.' — reference '.$this->booking->booking_reference.'.',
            'action_url' => route('partner.bookings.show', $this->booking),
            'booking_id' => $this->booking->id,
        ];
    }
}
