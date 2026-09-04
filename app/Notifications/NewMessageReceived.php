<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewMessageReceived extends Notification
{
    public function __construct(
        public Message $message,
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
            'title' => 'New message',
            'message' => $this->message->sender->name.': '.Str::limit($this->message->body, 80),
            'action_url' => route('messages.show', $this->message->conversation_id),
            'conversation_id' => $this->message->conversation_id,
        ];
    }
}
