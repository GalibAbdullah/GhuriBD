<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

class BookingPaid extends Notification
{
    /**
     * @param  'traveler'|'partner'  $audience  Whose notification this is — the
     *                                          message and link differ per side.
     * @param  string|null  $label  What was booked, from the partner's point of
     *                              view (e.g. "resort" or "tour package").
     */
    public function __construct(
        public Booking $booking,
        public string $audience = 'traveler',
        public ?string $label = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if ($this->audience === 'partner') {
            return [
                'title' => 'Payment received',
                'message' => $this->booking->user->name.' paid for your '.($this->label ?? 'booking')
                    .' — reference '.$this->booking->booking_reference.'. The booking is now confirmed.',
                'action_url' => route('partner.bookings.show', $this->booking),
                'booking_id' => $this->booking->id,
            ];
        }

        return [
            'title' => 'Payment successful',
            'message' => 'Your payment for booking '.$this->booking->booking_reference
                .' went through and the booking is confirmed.',
            'action_url' => route('traveler.bookings.show', $this->booking),
            'booking_id' => $this->booking->id,
        ];
    }
}
