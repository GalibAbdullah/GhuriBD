<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Notifications\Notification;

class ComplaintSubmitted extends Notification
{
    public function __construct(
        public Complaint $complaint,
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
        return [
            'title' => 'New complaint submitted',
            'message' => $this->complaint->user->name.' filed a complaint: '.$this->complaint->subject,
            'action_url' => route('complaints.show', $this->complaint),
            'complaint_id' => $this->complaint->id,
        ];
    }
}
