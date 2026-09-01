<?php

namespace App\Notifications;

use App\Enums\UserRole;
use App\Models\Booking;
use Illuminate\Notifications\Notification;

class BookingCancelled extends Notification
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
        $isOwner = $notifiable->hasRole(UserRole::TRAVELER->value) && $notifiable->id === $this->booking->user_id;

        $message = $isOwner
            ? 'Your booking '.$this->booking->booking_reference.' has been cancelled.'
            : 'Booking '.$this->booking->booking_reference.' by '.$this->booking->user->name.' has been cancelled.';

        return [
            'title' => 'Booking cancelled',
            'message' => $message,
            'action_url' => $isOwner
                ? route('traveler.bookings.show', $this->booking)
                : route('partner.bookings.show', $this->booking),
            'booking_id' => $this->booking->id,
        ];
    }
}
