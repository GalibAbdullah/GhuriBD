<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Notifications\Notification;

class ComplaintResponded extends Notification
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
            'title' => 'Your complaint was updated',
            'message' => 'Your complaint "'.$this->complaint->subject.'" is now '.$this->complaint->status.'.',
            'action_url' => route('complaints.show', $this->complaint),
            'complaint_id' => $this->complaint->id,
        ];
    }
}
