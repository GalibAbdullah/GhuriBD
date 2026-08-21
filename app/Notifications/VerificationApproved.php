<?php

namespace App\Notifications;

use App\Models\ProviderVerification;
use Illuminate\Notifications\Notification;

class VerificationApproved extends Notification
{

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ProviderVerification $verification,
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
            'title' => 'Your verification request has been approved.',
            'message' => 'Your Travel Partner verification for "'.$this->verification->provider_name.'" has been approved. You are now a verified provider.',
            'action_url' => route('partner.verifications.show', $this->verification),
            'verification_id' => $this->verification->id,
        ];
    }
}