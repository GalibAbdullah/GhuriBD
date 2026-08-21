<?php

namespace App\Notifications;

use App\Models\ProviderVerification;
use Illuminate\Notifications\Notification;

class VerificationRequestSubmitted extends Notification
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
            'title' => 'New Travel Partner Verification Request',
            'message' => $this->verification->user->name.' submitted a new verification request for "'.$this->verification->provider_name.'".',
            'action_url' => route('admin.verifications.show', $this->verification),
            'verification_id' => $this->verification->id,
        ];
    }
}